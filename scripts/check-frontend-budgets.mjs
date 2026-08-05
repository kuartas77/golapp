import { readFile, readdir, stat } from 'node:fs/promises'
import { gzipSync } from 'node:zlib'
import path from 'node:path'
import process from 'node:process'

const root = process.cwd()
const manifestPath = path.join(root, 'public/build/manifest.json')
const budgetsPath = path.join(root, 'config/frontend-budgets.json')
const assetsDirectory = path.join(root, 'public/build')
const legacyScssDirectory = path.join(root, 'resources/js/assets/sass')

const readJson = async file => JSON.parse(await readFile(file, 'utf8'))
const bytesLabel = bytes => `${(bytes / 1024).toFixed(1)} KiB`

const fileMetrics = async relativePath => {
    const absolutePath = path.join(assetsDirectory, relativePath)
    const contents = await readFile(absolutePath)

    return {
        bytes: contents.byteLength,
        gzipBytes: gzipSync(contents).byteLength,
    }
}

const collectFiles = async (directory, extension) => {
    const entries = await readdir(directory, { withFileTypes: true })
    const nestedFiles = await Promise.all(entries.map(async entry => {
        const target = path.join(directory, entry.name)

        if (entry.isDirectory()) {
            return collectFiles(target, extension)
        }

        return entry.name.endsWith(extension) ? [target] : []
    }))

    return nestedFiles.flat()
}

const manifest = await readJson(manifestPath)
const budgets = await readJson(budgetsPath)
const entryKey = Object.keys(manifest).find(key => manifest[key].isEntry && manifest[key].src === 'resources/js/main.js')

if (!entryKey) {
    throw new Error('No se encontró la entrada resources/js/main.js en el manifest de Vite.')
}

const importedKeys = new Set()
const collectImports = key => {
    if (importedKeys.has(key) || !manifest[key]) return

    importedKeys.add(key)
    ;(manifest[key].imports ?? []).forEach(collectImports)
}
collectImports(entryKey)

const entry = manifest[entryKey]
const entryJavascript = await fileMetrics(entry.file)
const initialJavascriptFiles = [...importedKeys]
    .map(key => manifest[key]?.file)
    .filter(file => file?.endsWith('.js'))
const initialJavascriptMetrics = await Promise.all(initialJavascriptFiles.map(fileMetrics))
const initialJavascriptBytes = initialJavascriptMetrics.reduce((total, metrics) => total + metrics.bytes, 0)

const entryCssFiles = entry.css ?? []
const entryCssMetrics = await Promise.all(entryCssFiles.map(fileMetrics))
const mainEntryCssBytes = entryCssMetrics.reduce((total, metrics) => total + metrics.bytes, 0)
const mainEntryCssGzipBytes = entryCssMetrics.reduce((total, metrics) => total + metrics.gzipBytes, 0)

const javascriptAssets = (await collectFiles(path.join(assetsDirectory, 'assets'), '.js'))
const javascriptAssetSizes = await Promise.all(javascriptAssets.map(async file => ({
    file,
    bytes: (await stat(file)).size,
})))
const largestJavascriptChunk = javascriptAssetSizes.sort((left, right) => right.bytes - left.bytes)[0]

const legacyScssFiles = await collectFiles(legacyScssDirectory, '.scss')
const legacyScssSourceBytes = (await Promise.all(legacyScssFiles.map(file => stat(file))))
    .reduce((total, fileStat) => total + fileStat.size, 0)

const checks = [
    ['JavaScript de entrada', entryJavascript.bytes, budgets.mainEntryJavascriptBytes],
    ['JavaScript de entrada gzip', entryJavascript.gzipBytes, budgets.mainEntryJavascriptGzipBytes],
    ['JavaScript inicial', initialJavascriptBytes, budgets.initialJavascriptBytes],
    [`Chunk JavaScript mayor (${path.basename(largestJavascriptChunk.file)})`, largestJavascriptChunk.bytes, budgets.largestJavascriptChunkBytes],
    ['CSS de entrada', mainEntryCssBytes, budgets.mainEntryCssBytes],
    ['CSS de entrada gzip', mainEntryCssGzipBytes, budgets.mainEntryCssGzipBytes],
    ['SCSS heredado', legacyScssSourceBytes, budgets.legacyScssSourceBytes],
]

let failed = false
for (const [label, actual, limit] of checks) {
    const passes = actual <= limit
    failed ||= !passes
    console.log(`${passes ? 'PASS' : 'FAIL'} ${label}: ${bytesLabel(actual)} / ${bytesLabel(limit)}`)
}

if (failed) {
    console.error('Los presupuestos frontend fueron excedidos. Reduce el bundle o ajusta el límite con evidencia en un commit separado.')
    process.exitCode = 1
}

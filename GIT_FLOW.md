# Guía rápida de Git Flow

El repositorio ya cuenta con las ramas `main` y `develop`, y utiliza `git-flow`.

## Inicialización

Ejecuta:

```bash
git flow init
```

Utiliza la siguiente configuración:

```text
Production branch: main
Development branch: develop
Feature prefix: feature/
Bugfix prefix: bugfix/
Release prefix: release/
Hotfix prefix: hotfix/
Support prefix: support/
Version tag prefix: [vacío]
```

> Evita `git flow init -d`, ya que podría seleccionar `master` en lugar de `main`.

## Funcionalidades

Para iniciar una funcionalidad:

```bash
git flow feature start mi-funcionalidad
```

Para finalizarla e integrarla en `develop`:

```bash
git flow feature finish mi-funcionalidad
```

## Releases

Para iniciar una versión:

```bash
git flow release start 1.2.0
```

Para finalizarla:

```bash
git flow release finish 1.2.0
```

## Hotfixes

Para iniciar una corrección urgente desde `main`:

```bash
git flow hotfix start 1.2.1
```

Para finalizarla:

```bash
git flow hotfix finish 1.2.1
```

## Publicación

Después de finalizar un release o hotfix, publica las ramas y etiquetas:

```bash
git push origin main develop --follow-tags
```

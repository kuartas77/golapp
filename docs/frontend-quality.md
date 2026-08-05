# Calidad frontend antes del despliegue

Esta verificación es independiente del proceso de despliegue y no modifica sus workflows.

## Comando completo

```bash
pnpm run quality:predeploy
```

El comando ejecuta, en este orden:

1. build de producción;
2. presupuestos de bundles y SCSS heredado;
3. lint de JavaScript y Vue;
4. Vitest;
5. Playwright, incluidos Axe, reflow a 360 px y foco de modales.

## Presupuestos

Los límites viven en `config/frontend-budgets.json`. La comprobación requiere un manifest generado por Vite y falla cuando crece cualquiera de estas líneas base:

- JavaScript de entrada, normal y gzip;
- JavaScript inicial, incluidos imports estáticos;
- chunk JavaScript individual más grande;
- CSS de entrada, normal y gzip;
- volumen de fuentes SCSS heredadas.

Un aumento de límite debe quedar justificado con evidencia en un commit separado. La reducción progresiva del CSS se realiza migrando reglas de `resources/js/assets/sass` hacia estilos acotados por componente y bajando después `legacyScssSourceBytes`; el presupuesto impide que la deuda vuelva a crecer entre migraciones.

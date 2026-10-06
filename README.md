# DAW - Guía Rápida de Git

## 🚀 Rutina Diaria (Paso a Paso)

```bash
# 1. Descargar siempre la última versión antes de empezar
git pull

# 2. Preparar los cambios realizados
git add .

# 3. Guardar el punto de control (Commit)
git commit -m "Descripción de los cambios"

# 4. Subir a GitHub
git push origin main




git clone https://github.com/librosinformatica12-sudo/DAW.git




# 1. Descargar e integrar los cambios de GitHub encima de los tuyos
git pull --rebase origin main

# 2. Volver a subir tus cambios
git push origin main

# 2. Verificación: Los archivos en conflicto aparecerán marcados en rojo dentro de Unmerged paths.
git status

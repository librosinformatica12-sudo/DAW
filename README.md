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




# 🚨 Guía de Solución: Conflictos al hacer `git pull --rebase`

Cuando ejecutas `git pull --rebase origin main` y tus archivos locales chocan con los de GitHub, Git pausa el proceso y te muestra el mensaje:  
`CONFLICT (content): Merge conflict in...`

Sigue estos **4 pasos** para resolverlo sin perder nada de código:

---

## 🛠️ Paso a Paso para Resolver el Conflicto

### 1️⃣ Identificar los archivos con conflicto
Consulta qué archivos están atascados ejecutando:
```bash
git status

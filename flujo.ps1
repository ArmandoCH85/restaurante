# ⚙️ Configura aquí tu rama personal
$miRama = "Pablo"

Write-Host "✅ Cambiando a la rama: $miRama"
git checkout $miRama

Write-Host "🔄 Haciendo fetch y rebase de origin/main"
git fetch origin
git rebase origin/main

Write-Host "🔍 Estado actual del repositorio:"
git status

Write-Host "`n🚀 Ya puedes trabajar en tu código."
Write-Host "   Cuando termines, ejecuta:"
Write-Host "   git add ."
Write-Host "   git commit -m 'mensaje'"
Write-Host "   git push origin $miRama"

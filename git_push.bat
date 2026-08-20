@echo off
echo ============================================================
echo   Pushing Seychelles Cargo Website to GitHub...
echo ============================================================
cd /d "c:\xampp\htdocs\seychelles"

git init
git add .
git commit -m "Seychelles Cargo website upgrade with PHP 8+ admin panel, Font Awesome 6 icons, CBM calculator, and vessel schedules"
git branch -M main
git remote remove origin 2>nul
git remote add origin https://github.com/nlshad/seychelles.git
git push -u origin main

echo ============================================================
echo   Done! Check https://github.com/nlshad/seychelles.git
echo ============================================================
pause

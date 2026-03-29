# rebrand.ps1
$search = "Coolify"
$replace = "Helix Claude"
$path = "d:\Aureon Company\Coolify deploy engine"

$files = Get-ChildItem -Path $path -Filter *.php -Recurse | Where-Object { $_.FullName -notmatch "vendor" -and $_.FullName -notmatch "node_modules" -and $_.FullName -notmatch ".git" }
$files += Get-ChildItem -Path $path -Filter *.blade.php -Recurse | Where-Object { $_.FullName -notmatch "vendor" -and $_.FullName -notmatch "node_modules" -and $_.FullName -notmatch ".git" }
$files += Get-ChildItem -Path $path -Filter *.js -Recurse | Where-Object { $_.FullName -notmatch "vendor" -and $_.FullName -notmatch "node_modules" -and $_.FullName -notmatch ".git" }

foreach ($file in $files) {
    (Get-Content -Path $file.FullName) | ForEach-Object { $_ -replace $search, $replace } | Set-Content -Path $file.FullName
}
Write-Host "Rebranding complete!"

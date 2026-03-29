# helix-deploy.ps1
$repoUrl = "https://github.com/mb2638512-a11y/Helix-Claude-.git"
$token = Read-Host -Prompt "Enter your GitHub Personal Access Token (PAT)" -AsSecureString
$bstr = [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($token)
$plainToken = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto($bstr)

$authenticatedUrl = $repoUrl.Replace("https://", "https://$plainToken@")

Write-Host "Syncing with Helix Claude repository..."
git remote set-url helix $authenticatedUrl
git push helix main

if ($LASTEXITCODE -eq 0) {
    Write-Host "Success! Code pushed to mb2638512-a11y/Helix-Claude-" -ForegroundColor Green
} else {
    Write-Host "Error: Push failed. Please check your token permissions." -ForegroundColor Red
}

# Optional: Cloudflare Wrangler deploy
Write-Host "Attempting Cloudflare deployment..."
npx wrangler pages deploy public --project-name helix-claude

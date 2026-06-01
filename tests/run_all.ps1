param(
    [switch]$KeepContainers
)

$ErrorActionPreference = 'Stop'
$ComposeArgs = @('compose', '-f', 'docker-compose.test.yml')

function Run-Native {
    param(
        [string]$FilePath,
        [string[]]$Arguments = @()
    )

    & $FilePath @Arguments
    if ($LASTEXITCODE -ne 0) {
        throw "Comando falhou ($LASTEXITCODE): $FilePath $($Arguments -join ' ')"
    }
}

function Run-Step {
    param(
        [string]$Name,
        [scriptblock]$Command
    )

    Write-Host ""
    Write-Host "==> $Name"
    & $Command
}

try {
    Run-Step "PHP unitarios locais" {
        Run-Native php @('tests/run.php')
    }

    Run-Step "Lint PHP local" {
        Get-ChildItem -Recurse -Filter *.php |
            Where-Object { $_.FullName -notmatch '\\database\\legacy_dumps\\' } |
            ForEach-Object { Run-Native php @('-l', $_.FullName) }
    }

    Run-Step "Config Docker de teste" {
        Run-Native docker ($ComposeArgs + @('config', '--quiet'))
    }

    Run-Step "Recriar ambiente Docker de teste" {
        Run-Native docker ($ComposeArgs + @('down', '-v', '--remove-orphans'))
        Run-Native docker ($ComposeArgs + @('up', '-d', '--build'))
    }

    Run-Step "Aguardar aplicacao HTTP" {
        $ready = $false
        for ($i = 1; $i -le 60; $i++) {
            try {
                $response = Invoke-WebRequest -Uri 'http://localhost:8090/' -UseBasicParsing -TimeoutSec 3
                if ($response.StatusCode -eq 200) {
                    $ready = $true
                    break
                }
            } catch {
                Start-Sleep -Seconds 2
            }
        }

        if (-not $ready) {
            throw "Aplicacao nao respondeu em http://localhost:8090/"
        }
    }

    Run-Step "Aguardar banco pelo container app" {
        Run-Native docker ($ComposeArgs + @('exec', '-T', 'app', 'php', 'tests/wait_for_database.php'))
    }

    Run-Step "Testes de integracao com banco" {
        Run-Native docker ($ComposeArgs + @('exec', '-T', 'app', 'php', 'tests/run_integration.php'))
    }

    Run-Step "Smoke tests HTTP" {
        Run-Native docker ($ComposeArgs + @('exec', '-T', 'app', 'php', 'tests/run_http_smoke.php'))
    }

    Run-Step "Backup e restauracao do banco" {
        Run-Native docker ($ComposeArgs + @('exec', '-T', 'db', 'sh', '-lc', "tr -d '\r' < /backup_restore_check.sh | sh"))
    }
} finally {
    if (-not $KeepContainers) {
        docker @ComposeArgs down -v --remove-orphans
    } else {
        Write-Host ""
        Write-Host "Containers mantidos para inspecao: docker compose -f docker-compose.test.yml ps"
    }
}

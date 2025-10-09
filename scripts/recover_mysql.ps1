<#
Safe MySQL/MariaDB recovery helper for XAMPP on Windows
- Creates a timestamped backup of C:\xampp\mysql\data using robocopy
- Lists contents of the data folder and any XAMPP mysql backups
- Backs up my.ini and adds innodb_force_recovery=1 under [mysqld] (if not present)
- Optionally attempts to start mysqld in the background and, if started, runs mysqldump --all-databases

USAGE (run as Administrator in PowerShell):
    powershell -ExecutionPolicy Bypass -File "C:\xampp\htdocs\HIMSDrRomelCruz\scripts\recover_mysql.ps1"

IMPORTANT SAFETY NOTES:
- This script NEVER deletes files. It only copies and writes backups.
- It will add `innodb_force_recovery=1` to my.ini to allow read-only startup if needed. This setting MUST be removed after you finish the dump.
- If you are unsure, stop here and paste the logs produced by this script so I can analyze them.
#>

# NOTE: Previously this script required Administrator. We no longer abort automatically so the
# script will attempt safe operations under the current account and report permission errors
# if they occur. For best results run PowerShell as Administrator.

# Variables
$timestamp = Get-Date -Format yyyyMMdd_HHmmss
$dataDir = 'C:\xampp\mysql\data'
$backupRoot = "C:\xampp\mysql\recovery_backups"
$backupDir = Join-Path $backupRoot "data_backup_$timestamp"
$logDir = 'C:\xampp\mysql\recovery_logs'
New-Item -ItemType Directory -Force -Path $backupRoot | Out-Null
New-Item -ItemType Directory -Force -Path $logDir | Out-Null
$consoleLog = Join-Path $logDir "mysqld_console_$timestamp.log"
$dumpFile = "C:\xampp\mysql\recovery_dump_$timestamp.sql"
$myIni = 'C:\xampp\mysql\bin\my.ini'
$myIniBak = "${myIni}.bak_$timestamp"

Write-Output "[1/6] Backing up data directory..."
if (-not (Test-Path $dataDir)) {
    Write-Error "Data directory $dataDir does not exist. Aborting."
    exit 1
}

# Use robocopy to mirror the data directory to backup dir
$robocopyArgs = "`"$dataDir`" `"$backupDir`" /MIR /R:2 /W:2"
Write-Output "Running: robocopy $robocopyArgs"
robocopy $dataDir $backupDir /MIR /R:2 /W:2 | Out-File -FilePath (Join-Path $logDir "robocopy_$timestamp.txt") -Encoding utf8
Write-Output "Backup created at: $backupDir"

Write-Output "[2/6] Listing data directory contents..."
Get-ChildItem -Path $dataDir -Force | Sort-Object LastWriteTime -Descending | Select-Object Name, Length, LastWriteTime | Out-File -FilePath (Join-Path $logDir "data_dir_listing_$timestamp.txt") -Encoding utf8
Get-Content -Path (Join-Path $logDir "data_dir_listing_$timestamp.txt") | Write-Output

Write-Output "[3/6] Checking for XAMPP mysql backup folder..."
if (Test-Path 'C:\xampp\mysql\backup') {
    Get-ChildItem 'C:\xampp\mysql\backup' -Recurse | Select-Object FullName, LastWriteTime | Out-File -FilePath (Join-Path $logDir "xampp_backup_listing_$timestamp.txt") -Encoding utf8
    Write-Output "Found C:\xampp\mysql\backup. Listing saved to recovery_logs."
} else {
    Write-Output "No C:\xampp\mysql\backup folder found."
}

Write-Output "[4/6] Backing up my.ini and adding innodb_force_recovery=1 (if missing)..."
if (-not (Test-Path $myIni)) {
    Write-Error "my.ini not found at $myIni. Please check XAMPP installation path. Aborting."
    exit 1
}
Copy-Item -Path $myIni -Destination $myIniBak -Force
(Get-Content $myIni) | Out-File -FilePath (Join-Path $logDir "myini_original_$timestamp.ini") -Encoding utf8

$myiniContent = Get-Content $myIni -Raw
if ($myiniContent -match '(?ms)^[\[]mysqld[\].*?innodb_force_recovery\s*=') {
    Write-Output "innodb_force_recovery entry already present in my.ini. No change made."
} else {
    # Insert innodb_force_recovery=1 under [mysqld]
    $lines = Get-Content $myIni
    $out = @()
    $inserted = $false
    for ($i=0; $i -lt $lines.Count; $i++) {
        $out += $lines[$i]
        if (-not $inserted -and $lines[$i] -match '^[\[]mysqld[\]') {
            # find next non-empty location to insert (after the section header)
            # we'll insert right after the mysqld header line
            $out += 'innodb_force_recovery=1'
            $inserted = $true
        }
    }
    if (-not $inserted) {
        # fallback: append to end
        $out += '[mysqld]'
        $out += 'innodb_force_recovery=1'
    }
    $out -join "`r`n" | Out-File -FilePath $myIni -Encoding utf8
    Write-Output "Added innodb_force_recovery=1 to my.ini and saved backup to $myIniBak"
}

Write-Output "[5/6] Instructing to start MariaDB and capturing console output..."
Write-Output "You can start MariaDB from the XAMPP Control Panel now OR let this script attempt to start it in background."

# Attempt to start mysqld in a background job if not already running
$mysqldProcess = Get-Process -Name mysqld -ErrorAction SilentlyContinue
if ($mysqldProcess) {
    Write-Output "mysqld process already running (PID: $($mysqldProcess.Id)). Will not attempt to start a second instance."
} else {
    Write-Output "Attempting to start mysqld in background job. Console output will be written to $consoleLog"
    # Start a background job that runs mysqld.exe --console and redirects output to file
    Start-Job -Name "StartMySQL_$timestamp" -ScriptBlock {
        Set-Location 'C:\xampp\mysql\bin'
        # redirect stdout & stderr to log file
        & '.\mysqld.exe' --console *> 'C:\xampp\mysql\recovery_logs\mysqld_console_BACKGROUND.log'
    } | Out-Null
    Write-Output "Background job started. Wait 15 seconds then check $consoleLog (or the recovery_logs folder) for messages."
}

Write-Output "[6/6] Waiting 15 seconds, then checking if port 3306 is listening..."
Start-Sleep -Seconds 15
$listener = netstat -ano -p tcp | Select-String ":3306"
if ($listener) {
    $pid = ($listener -split '\s+')[-1]
    Write-Output "Port 3306 is in use by PID: $pid"
    try { Get-Process -Id $pid -ErrorAction Stop | Select-Object Id, ProcessName | Format-Table -AutoSize } catch { Write-Output "Process $pid not found." }
    # Attempt a mysqldump if mysqld is the listener
    $proc = Get-Process -Id $pid -ErrorAction SilentlyContinue
    if ($proc -and $proc.ProcessName -match 'mysqld') {
        Write-Output "Detected mysqld listening on 3306. Attempting mysqldump to $dumpFile"
        Push-Location 'C:\xampp\mysql\bin'
        # Adjust user/password as needed; this assumes root without password. If root has a password, run the dump manually with -p
        $dumpCmd = ".\mysqldump.exe --user=root --all-databases --result-file=`"$dumpFile`""
        Write-Output "Running: $dumpCmd"
        & .\mysqldump.exe --user=root --all-databases --result-file=$dumpFile 2>&1 | Out-File -FilePath (Join-Path $logDir "mysqldump_output_$timestamp.txt") -Encoding utf8
        Pop-Location
        if (Test-Path $dumpFile) { Write-Output "mysqldump completed and saved to $dumpFile" } else { Write-Output "mysqldump did not produce $dumpFile. Check mysqldump output log in recovery_logs." }
    } else {
        Write-Output "Port 3306 is used by another process. Do not proceed with mysqldump. Please start mysqld from XAMPP control panel or run mysqld.exe --console manually and paste the console log here."
    }
} else {
    Write-Output "No listener on port 3306 detected. If you started mysqld from XAMPP, check the recovery_logs folder for mysqld console output."
}

Write-Output "
Script finished. Important next steps (manual):
- If the server started under innodb_force_recovery, immediately create logical dumps (mysqldump) of all databases and verify the dump file.
- Remove the line innodb_force_recovery=1 from my.ini after you have a verified dump and backups.
- If mysqld did not start, please copy the console log file from C:\xampp\mysql\recovery_logs and paste it here.

Generated logs are in: $logDir
Backup is in: $backupDir
Dump (if created): $dumpFile
"

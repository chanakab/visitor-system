@echo off
TITLE Auto Push Service - Visitor System
ECHO Starting Auto Push Service...
ECHO Press Ctrl+C to stop.

:loop
CLS
ECHO Checking for changes...
cd /d "%~dp0"

:: Add all changes
git add .

:: Commit with timestamp (suppress output if nothing to commit)
git commit -m "Auto commit: %date% %time%" >nul 2>&1
IF %ERRORLEVEL% EQU 0 (
    ECHO [+] Changes committed.
    ECHO [^>] Pushing to remote...
    git push origin main
) ELSE (
    ECHO [-] No changes detected.
)

:: Wait 60 seconds
timeout /t 60
goto loop

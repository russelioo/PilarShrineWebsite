@echo off
cd /d "C:\BU RDMD System\PilarShrineWebsite"
if not exist vendor exit /b 0
takeown /f vendor /r /d y
icacls vendor /grant %USERNAME%:(OI)(CI)F /t /c
attrib -r vendor /s /d
rmdir /s /q vendor
if exist vendor exit /b 1

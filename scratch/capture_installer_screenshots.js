import { spawn, execSync } from 'child_process';
import { writeFileSync, mkdirSync } from 'fs';
import { join } from 'path';

const CHROME_PATH = "C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe";
const OUTPUT_DIR = "D:\\xampp\\htdocs\\retail-pos\\docs\\screenshots";
const PROFILE_DIR = "C:\\Users\\PVT. LTD\\.gemini\\antigravity\\brain\\039796d7-fe0b-43ed-a999-9c8431364232\\scratch\\chrome-installer-test";

try {
  mkdirSync(OUTPUT_DIR, { recursive: true });
  mkdirSync(PROFILE_DIR, { recursive: true });
} catch (e) {}

async function wait(ms) {
  return new Promise(r => setTimeout(r, ms));
}

async function run() {
  console.log('Resetting installer to test fresh installation screens...');
  execSync('php artisan app:reset-installer --force', { cwd: 'D:\\xampp\\htdocs\\retail-pos' });

  const chrome = spawn(CHROME_PATH, [
    '--headless=new',
    '--remote-debugging-port=9222',
    `--user-data-dir=${PROFILE_DIR}`,
    '--disable-gpu',
    '--disable-extensions',
    '--no-sandbox',
    '--window-size=1440,900',
    'http://127.0.0.1:8000/install'
  ]);

  await wait(2500);

  try {
    const listRes = await fetch('http://127.0.0.1:9222/json/list');
    const listData = await listRes.json();
    const target = listData.find(t => t.type === 'page') || listData[0];

    const ws = new WebSocket(target.webSocketDebuggerUrl);
    let msgId = 1;
    const callbacks = new Map();

    ws.onmessage = (event) => {
      const msg = JSON.parse(event.data);
      if (msg.id && callbacks.has(msg.id)) {
        callbacks.get(msg.id)(msg.result);
        callbacks.delete(msg.id);
      }
    };

    function send(method, params = {}) {
      return new Promise((resolve) => {
        const id = msgId++;
        callbacks.set(id, resolve);
        ws.send(JSON.stringify({ id, method, params }));
      });
    }

    await new Promise(r => ws.onopen = r);
    await send('Page.enable');
    await send('Runtime.enable');

    // 1. Capture Step 1 Welcome
    console.log('Capturing Step 1: Welcome...');
    await send('Page.navigate', { url: 'http://127.0.0.1:8000/install' });
    await wait(3000);
    let shot = await send('Page.captureScreenshot', { format: 'png' });
    writeFileSync(join(OUTPUT_DIR, 'installer_01_welcome.png'), Buffer.from(shot.data, 'base64'));

    // 2. Go to Step 2 Requirements
    console.log('Capturing Step 2: System Requirements...');
    await send('Runtime.evaluate', {
      expression: `document.querySelector('button.btn-primary').click();`
    });
    await wait(2500);
    shot = await send('Page.captureScreenshot', { format: 'png' });
    writeFileSync(join(OUTPUT_DIR, 'installer_02_requirements.png'), Buffer.from(shot.data, 'base64'));

    // 3. Switch to Arabic RTL on Step 2
    console.log('Capturing Step 2: Arabic RTL Localization...');
    await send('Runtime.evaluate', {
      expression: `
        // Click language dropdown and select Arabic
        const dropdownItem = Array.from(document.querySelectorAll('.dropdown-item')).find(el => el.textContent.includes('العربية'));
        if (dropdownItem) dropdownItem.click();
      `
    });
    await wait(2000);
    shot = await send('Page.captureScreenshot', { format: 'png' });
    writeFileSync(join(OUTPUT_DIR, 'installer_03_arabic_rtl.png'), Buffer.from(shot.data, 'base64'));

    // Switch back to English
    await send('Runtime.evaluate', {
      expression: `
        const dropdownItem = Array.from(document.querySelectorAll('.dropdown-item')).find(el => el.textContent.includes('English'));
        if (dropdownItem) dropdownItem.click();
      `
    });
    await wait(1000);

    // 4. Mark installed and capture Already Installed protection screen
    console.log('Marking installed and capturing protection screen...');
    execSync('php artisan app:mark-installed', { cwd: 'D:\\xampp\\htdocs\\retail-pos' });
    await send('Page.navigate', { url: 'http://127.0.0.1:8000/install' });
    await wait(2500);
    shot = await send('Page.captureScreenshot', { format: 'png' });
    writeFileSync(join(OUTPUT_DIR, 'installer_04_already_installed.png'), Buffer.from(shot.data, 'base64'));

    console.log('All installer screenshots captured successfully!');
    ws.close();
  } catch (err) {
    console.error('Capture error:', err);
  } finally {
    chrome.kill();
    // Keep app marked as installed so local environment is ready for normal use
    execSync('php artisan app:mark-installed', { cwd: 'D:\\xampp\\htdocs\\retail-pos' });
    process.exit(0);
  }
}

run();

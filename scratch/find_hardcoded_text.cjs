const fs = require('fs');
const path = require('path');

const vueDir = path.join(__dirname, '..', 'resources', 'js');
const hardcoded = [];

function scanFile(filePath) {
    const content = fs.readFileSync(filePath, 'utf8');
    const templateMatch = content.match(/<template>([\s\S]*?)<\/template>/);
    if (!templateMatch) return;

    const template = templateMatch[1];
    const lines = template.split('\n');

    lines.forEach((line, index) => {
        // Look for text between tags: >Some English Text<
        const textMatches = line.matchAll(/>\s*([A-Za-z][A-Za-z0-9 ,.!?'-]{3,})\s*</g);
        for (const m of textMatches) {
            const txt = m[1].trim();
            // Ignore code-like or icon-like
            if (!txt.includes('{{') && !txt.startsWith('v-') && !txt.startsWith('http')) {
                hardcoded.push({
                    file: path.relative(path.join(__dirname, '..'), filePath),
                    line: index + 1,
                    text: txt
                });
            }
        }
    });
}

function walk(dir) {
    const files = fs.readdirSync(dir);
    for (const f of files) {
        const full = path.join(dir, f);
        if (fs.statSync(full).isDirectory()) {
            if (f !== 'node_modules' && f !== '.git') walk(full);
        } else if (f.endsWith('.vue')) {
            scanFile(full);
        }
    }
}

walk(vueDir);

console.log(`Found ${hardcoded.length} possible hardcoded text occurrences:`);
console.log(hardcoded.slice(0, 50));

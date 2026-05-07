const fs = require('fs');
const path = require('path');

const viewsDir = path.join(__dirname, 'resources', 'views');

const replacements = [
    { regex: /\bbg-blue-100\b/g, replacement: 'bg-blue-900/30' },
    { regex: /\bbg-green-100\b/g, replacement: 'bg-green-900/30' },
    { regex: /\bbg-yellow-100\b/g, replacement: 'bg-yellow-900/30' },
    { regex: /\bbg-red-100\b/g, replacement: 'bg-red-900/30' },
    { regex: /\bbg-purple-100\b/g, replacement: 'bg-purple-900/30' },
    { regex: /\btext-blue-700\b/g, replacement: 'text-blue-400' },
    { regex: /\btext-blue-800\b/g, replacement: 'text-blue-300' },
    { regex: /\btext-green-700\b/g, replacement: 'text-green-400' },
    { regex: /\btext-green-800\b/g, replacement: 'text-green-300' },
    { regex: /\btext-yellow-700\b/g, replacement: 'text-yellow-400' },
    { regex: /\btext-yellow-800\b/g, replacement: 'text-yellow-300' },
    { regex: /\btext-red-700\b/g, replacement: 'text-red-400' },
    { regex: /\btext-red-800\b/g, replacement: 'text-red-300' },
    { regex: /\btext-purple-700\b/g, replacement: 'text-purple-400' },
    { regex: /\btext-purple-800\b/g, replacement: 'text-purple-300' },
];

function processDirectory(dir) {
    const files = fs.readdirSync(dir);

    for (const file of files) {
        const fullPath = path.join(dir, file);
        const stat = fs.statSync(fullPath);

        if (stat.isDirectory()) {
            processDirectory(fullPath);
        } else if (fullPath.endsWith('.blade.php')) {
            let content = fs.readFileSync(fullPath, 'utf8');
            let modified = false;

            for (const r of replacements) {
                if (r.regex.test(content)) {
                    content = content.replace(r.regex, r.replacement);
                    modified = true;
                }
            }

            if (modified) {
                fs.writeFileSync(fullPath, content, 'utf8');
                console.log(`Updated badges: ${fullPath}`);
            }
        }
    }
}

processDirectory(viewsDir);
console.log('Badge theme conversion complete.');

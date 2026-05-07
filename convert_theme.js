const fs = require('fs');
const path = require('path');

const viewsDir = path.join(__dirname, 'resources', 'views');

const replacements = [
    { regex: /\bbg-white\b/g, replacement: 'bg-gray-900' },
    { regex: /\bbg-gray-50\b/g, replacement: 'bg-gray-800' },
    { regex: /\bbg-gray-100\b/g, replacement: 'bg-gray-800' },
    { regex: /\btext-gray-900\b/g, replacement: 'text-gray-100' },
    { regex: /\btext-gray-800\b/g, replacement: 'text-gray-200' },
    { regex: /\btext-gray-700\b/g, replacement: 'text-gray-300' },
    { regex: /\btext-gray-600\b/g, replacement: 'text-gray-400' },
    { regex: /\btext-gray-500\b/g, replacement: 'text-gray-500' }, // Maybe keep
    { regex: /\bborder-gray-100\b/g, replacement: 'border-gray-800' },
    { regex: /\bborder-gray-200\b/g, replacement: 'border-gray-700' },
    { regex: /\bborder-gray-300\b/g, replacement: 'border-gray-600' },
    { regex: /\bhover:bg-gray-50\b/g, replacement: 'hover:bg-gray-800' },
    { regex: /\bhover:bg-gray-100\b/g, replacement: 'hover:bg-gray-700' },
    { regex: /\bdivide-gray-100\b/g, replacement: 'divide-gray-800' },
    { regex: /\bdivide-gray-200\b/g, replacement: 'divide-gray-700' },
    { regex: /\bring-gray-100\b/g, replacement: 'ring-gray-800' },
    { regex: /\bring-gray-200\b/g, replacement: 'ring-gray-700' },
    { regex: /\bbg-blue-50\b/g, replacement: 'bg-blue-900/30' },
    { regex: /\btext-blue-800\b/g, replacement: 'text-blue-300' },
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
                console.log(`Updated: ${fullPath}`);
            }
        }
    }
}

processDirectory(viewsDir);
console.log('Theme conversion complete.');

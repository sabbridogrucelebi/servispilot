const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

function fixEncoding(dir) {
    const files = fs.readdirSync(dir);
    for (const file of files) {
        const fullPath = path.join(dir, file);
        if (fs.statSync(fullPath).isDirectory()) {
            fixEncoding(fullPath);
        } else if (fullPath.endsWith('.js')) {
            const buffer = fs.readFileSync(fullPath);
            
            // Try to decode as UTF-8
            let isUtf8 = true;
            try {
                const str = buffer.toString('utf8');
                // If it contains replacement character \uFFFD, it's not valid UTF-8
                if (str.includes('\uFFFD')) {
                    isUtf8 = false;
                }
            } catch (e) {
                isUtf8 = false;
            }

            if (!isUtf8) {
                // Decode from cp1254 (Windows Turkish) to UTF-8
                const iconv = require('iconv-lite');
                const decoded = iconv.decode(buffer, 'win1254');
                fs.writeFileSync(fullPath, decoded, 'utf8');
                console.log('Fixed (cp1254 -> utf8): ' + fullPath);
            } else {
                // Already UTF-8, but what if it is double encoded?
                // Double encoded characters like Ã¼, Ä°, ÅŸ, Ã§
                let str = buffer.toString('utf8');
                if (str.includes('Ã¼') || str.includes('Ä°') || str.includes('ÅŸ') || str.includes('Ã§') || str.includes('Ã–')) {
                    const iconv = require('iconv-lite');
                    // Reverse the double encoding
                    const buffer2 = iconv.encode(str, 'latin1'); 
                    const decoded = iconv.decode(buffer2, 'utf8');
                    fs.writeFileSync(fullPath, decoded, 'utf8');
                    console.log('Fixed (Double Encoded -> utf8): ' + fullPath);
                } else {
                    console.log('Already valid UTF-8: ' + fullPath);
                }
            }
        }
    }
}

fixEncoding(path.join(__dirname, 'mobile-app/src'));
fixEncoding(path.join(__dirname, 'mobile-app/App.js'));

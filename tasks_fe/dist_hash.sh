#!/bin/bash

set -euo pipefail

hash=$(date +%s)

for extension in css jpg js html; do
    for filename in $(find dist/ -name "*.${extension}"); do
        filename=${filename//dist\//}
        filename_new=${filename//.${extension}/}.${hash}.${extension}

        if [ "${filename}" = "index.html" ]; then
            continue
        fi

        echo renaming ${filename} to ${filename_new}

        grep -rl ${filename} dist/ | xargs sed -i "s#${filename}#${filename_new}#g"
        mv dist/${filename} dist/${filename_new}
    done
done

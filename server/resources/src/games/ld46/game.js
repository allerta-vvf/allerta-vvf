import {default as createUnityInstance} from './ld46.loader.js';

import build_size_data from './ld46.data.gz';
import build_size_framework from './ld46.framework.js.gz';
import build_size_wasm from './ld46.wasm.gz';

console.log("data "+build_size_data);
console.log("framework "+build_size_framework);
console.log("wasm "+build_size_wasm);

export default class {
    constructor(){
        this.title = "What the firetruck";
        this.author = "dvdfu";
        this.author_url = "https://github.com/dvdfu";
    }

    initialize(container){
        container.append("<canvas id='unity-canvas' style='width: 768px; height: 512px; background: #231F20'></canvas>");
        createUnityInstance(document.querySelector("#unity-canvas"), {
            dataUrl: __webpack_public_path__ + "ld46.data.gz",
            frameworkUrl: __webpack_public_path__ + "ld46.framework.js.gz",
            codeUrl: __webpack_public_path__ + "ld46.wasm.gz",
            streamingAssetsUrl: "StreamingAssets",
            companyName: "dvdfu",
            productName: "ld46",
            productVersion: "0.1",
        });
    }
}
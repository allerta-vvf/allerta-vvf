import {default as createUnityInstance} from "./ld46.loader.js";

import buildSizeData from "./ld46.data.gz";
import buildSizeFramework from "./ld46.framework.js.gz";
import buildSizeWasm from "./ld46.wasm.gz";

console.log("data "+buildSizeData);
console.log("framework "+buildSizeFramework);
console.log("wasm "+buildSizeWasm);

export default class {
    constructor(){
        this.title = "What the firetruck";
        this.author = "dvdfu";
        this.authorUrl = "https://github.com/dvdfu";
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
export default class {
    constructor(){
        this.title = "Empty game";
        this.author = "Prova";
        this.authorUrl = "https://example.com/";
    }

    initialize(container){
        container.append("<b>THIS IS THE GAME</b>");
    }
}
export default class {
    constructor(){
        this.title = "Empty game";
        this.author = "Prova";
        this.author_url = "https://example.com/";
    }

    initialize(container){
        container.append("<b>THIS IS THE GAME</b>");
    }
}
const GAME_LOADING_ERROR_MSG = "Game loading failed, please retry later.";

async function play (game) {
  console.log("Opening game " + game + "...");
  try {
    await import(`./games/${game}/game.js`)
      .then(({ default: Game }) => {
        const game = new Game();
        $("body").append("<div class=\"modal\" id=\"modal_game\" tabindex=\"-1\" role=\"dialog\" data-backdrop=\"static\" style=\"display: none; min-width: 100%; margin: 0px;\"><div class=\"modal-dialog\" role=\"document\" style=\"min-width: 100%; margin: 0;\"><div class=\"modal-content\" style=\"min-height: 100vh;\" id=\"modal_game_content\"></div></div></div>");
        $("#modal_game_content").append(`<div class="modal-header"><h5 class="modal-title" style="color: black">${game.title}</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="modal-body" id="modal_game_body"></div>`);
        $("#modal_game_body").append(`<div id="game_content" style="text-align: center"></div><p style="text-align: right; color: black;">Game by <a style="color: blue" target="_blank" href="${game.authorUrl}">${game.author}</a></p>`);
        $("#modal_game").modal("show");
        game.initialize($("#game_content"));
      });
    $("#modal_game").on("hidden.bs.modal", function (e) {
      $("#modal_game").remove();
    });
  } catch (error) {
    console.error(error);
    toastr.error(GAME_LOADING_ERROR_MSG);
  }
}

$(function() {
  $(".playGame").each((num, elem) => {
    console.log(num, elem);
    let game = elem.getAttribute("data-game");
    if(game !== null){
      $(elem).on("click", function(){ play(game); });
    }
  });
});

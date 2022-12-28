var faceButton = document.querySelector(".bedhead");
faceButton.addEventListener("click",function(){
  playYawn();
})

var playedYawn = false;

var copyrightDate = document.querySelector(".copyrightdate");
copyrightDate.innerHTML = new Date().getFullYear();

function playYawn(){
  if (playedYawn !== true){
    var yawnClip = new Audio("sounds/yawn.ogg");
    yawnClip.play();
    playedYawn = true;
    console.log("Played Yawn??");
  }
}

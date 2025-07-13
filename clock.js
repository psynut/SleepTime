const monthNames = ["January", "February", "March", "April", "May", "June",
  "July", "August", "September", "October", "November", "December"
];
const dayNames = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"]

var thisYear = new Date().getFullYear();
var copyrightDate = document.querySelector(".copyrightdate");
copyrightDate.innerHTML = thisYear;

var hourDisplay = document.querySelector(".hour");
var minutesDisplay = document.querySelector(".minutes");
var secondsDisplay = document.querySelector(".seconds");
var aMPMDisplay = document.querySelector(".clock-ampm");
var dayOfWeekDisplay = document.querySelector(".dayofweek");
var monthDisplay = document.querySelector(".month");
var dayOfMonthDisplay = document.querySelector(".dateday");
var yearDisplay = document.querySelector(".year");

var thisHour = new Date().getHours();
var thisMinute = new Date().getMinutes();
var thisSecond = new Date().getSeconds();
var thisDayOfTheWeek = new Date().getDay();
var thisMonth = new Date().getMonth();
var thisDate = new Date().getDate();

var catRunningImages = [
  "images/Cat/BlackCat04.png","images/Cat/BlackCat05.png",
  "images/Cat/BlackCat06.png","images/Cat/BlackCat07.png","images/Cat/BlackCat08.png",
  "images/Cat/BlackCat09.png",
  "images/Cat/BlackCat10.png","images/Cat/BlackCat11.png","images/Cat/BlackCat12.png",
  "images/Cat/BlackCat00.png","images/Cat/BlackCat01.png","images/Cat/BlackCat02.png",
  "images/Cat/BlackCat03.png"
  ];
var catFrameCount = 0;
var runningImage = document.querySelector(".running-image");
var runningImageAngle = 0;
var runningImageXPos = 0;

ClearAfterFiveMinutes();
UpdateDisplay();

var tickTock = setInterval(AddSecond, 1000);
var screenSave = setInterval(Scooch, 1000*60*5); //Every 5 minutes the position changes
//var rotateImage = setInterval(SpinRunningImage, 17);
var scrollImage =setInterval(ScrollImage, 1000);
var animateImage = setInterval(CatsNextFrame,1000/13); 


/*        Clock Display Functions           */
/*------------------------------------------*/
function AddSecond(){
    const now = new Date();
    thisHour = now.getHours();
    thisMinute = now.getMinutes();
    thisSecond = now.getSeconds();
    thisDayOfTheWeek = now.getDay();
    thisMonth = now.getMonth();
    thisDate = now.getDate();
    thisYear = now.getFullYear();

    UpdateDisplay();
}

function UpdateDisplay(){
    hourDisplay.innerHTML = ((thisHour + 24) % 12 || 12).toString().padStart(2,'0')
    minutesDisplay.innerHTML = thisMinute.toString().padStart(2,'0');
    secondsDisplay.innerHTML = thisSecond.toString().padStart(2,'0');
    aMPMDisplay.innerHTML = (thisHour >= 12) ? "PM" : "AM";
    dayOfWeekDisplay.innerHTML = dayNames[thisDayOfTheWeek];
    monthDisplay.innerHTML = monthNames[thisMonth];
    dayOfMonthDisplay.innerHTML = thisDate.toString();
    yearDisplay.innerHTML = thisYear;  
}



/*          Animation Functions             */
/*------------------------------------------*/

function AnimateCatOnce(){
  CatsNextFrame();
  if(catFrameCount<catRunningImages.length){
    setTimeout(CatsNextFrame(),1000/14)
  }
}

function CatsNextFrame(){
  runningImage.src=catRunningImages[catFrameCount];
  catFrameCount++;
  if(catFrameCount >= catRunningImages.length){
    catFrameCount = 0;
  }
}

function ClearAfterFiveMinutes(){
  setTimeout(()=>{
    let clearItems = document.querySelectorAll(".clear-after-five");
    for(const m_Element of clearItems){
      m_Element.remove();
    }  
  },1000*60*5);  
}  

function FlipImageX(flipBool){
  runningImage.style.transform = flipBool ? "scale(-1,1)" : "scale(1,1)";
}  

function ScrollImage(){
  runningImageXPos = Math.abs((thisSecond-30))*(90/30)
  runningImage.style.position = "relative";
  runningImage.style.left = runningImageXPos+"%";
  FlipImageX(thisSecond < 30);
}  

function Scooch(){
  let xMovingElements = document.querySelectorAll(".pixel-shift-x");
  let yMovingElements = document.querySelectorAll(".pixel-shift-y");
  for(const m_Element of xMovingElements){
    let rndX = Math.floor((Math.random()*50)-25);
    m_Element.style.position = "relative";
    m_Element.style.left = rndX+'px';
  }
  for(const m_Element of yMovingElements){
    let rndY = Math.floor((Math.random()*50)-25);
    m_Element.style.position = "relative";
    m_Element.style.top = rndY+'px';
  }
  //body.style.position = "relative";
  //body.style.left = rndX+'px';
  console.log("Schooch" + rndX);
}

function SpinRunningImage(){
  runningImage.style.transform = "rotate("+runningImageAngle+"deg)";
  runningImageAngle++;
  if(runningImageAngle>360){
    runningImageAngle = 1;
  }  
}  


    

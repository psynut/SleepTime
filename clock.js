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

UpdateDisplay();


var tickTock = setInterval(AddSecond, 1000);

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
    aMPMDisplay = (thisHour >= 12) ? "PM" : "AM";
    dayOfWeekDisplay.innerHTML = dayNames[thisDayOfTheWeek];
    monthDisplay.innerHTML = monthNames[thisMonth];
    dayOfMonthDisplay.innerHTML = thisDate.toString();
    yearDisplay.innerHTML = thisYear;  
}
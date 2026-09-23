var ad = document.createElement("div");

ad.id = "advertisement";

ad.innerHTML = `
    <span id="adText">
        🎓 StudentSphere Special Offer! Explore Courses, Colleges and Career Resources.
    </span>

    <button id="closeAd">×</button>
`;

document.body.insertBefore(ad, document.body.firstChild);


var ads = [
    "🎓 Explore new courses and improve your skills!",
    "📚 Find study resources and previous papers on StudentSphere!",
    "💼 Discover internship and career opportunities!",
    "🏆 Participate in hackathons and coding competitions!",
    "🎓 Find the best colleges and courses for your career!"
];


var currentAd = 0;


setInterval(function() {

    currentAd++;

    if (currentAd >= ads.length) {

        currentAd = 0;

    }

    document.getElementById("adText").innerText = ads[currentAd];

}, 4000);


document.getElementById("closeAd").addEventListener("click", function() {

    ad.style.display = "none";

});
function makeResponsive() {
    let screenWidth = window.innerWidth;

    let navLinks = document.querySelectorAll("nav a");
    let banner = document.querySelector(".banner");
    let sections = document.querySelectorAll("section");
    let dashboardContainer = document.querySelector(".dashboard-container");
    let boxes = document.querySelectorAll(".box");
    let table = document.querySelector("table");
    let logout = document.querySelector(".logout");
    if (screenWidth <= 768) {
        navLinks.forEach(function(link) {
            link.style.display = "inline-block";
            link.style.margin = "8px";
        });
        banner.style.padding = "35px 15px";
        sections.forEach(function(section) {
            section.style.padding = "25px 15px";
        });
        dashboardContainer.style.flexDirection = "column";
        dashboardContainer.style.alignItems = "center";
        boxes.forEach(function(box) {
            box.style.width = "85%";
            box.style.marginBottom = "20px";
        });
        table.style.width = "100%";
        table.style.fontSize = "14px";
        logout.style.display = "inline-block";
        logout.style.margin = "8px";

    }
    else {
        navLinks.forEach(function(link) {
            link.style.display = "";
            link.style.margin = "15px";
        });
        banner.style.padding = "50px";
        sections.forEach(function(section) {
            section.style.padding = "40px";
        });
        dashboardContainer.style.flexDirection = "";
        dashboardContainer.style.alignItems = "";
        boxes.forEach(function(box) {
            box.style.width = "220px";
            box.style.marginBottom = "";
        });
        table.style.width = "85%";
        table.style.fontSize = "";
            logout.style.display = "";
        logout.style.margin = "";
    }
}
makeResponsive();
window.addEventListener("resize", makeResponsive);
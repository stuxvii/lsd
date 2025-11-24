const button = document.getElementById("button");
const warning = document.getElementById("warning");
const div = document.getElementById("div");
const win_xp_tb = document.getElementById("win_xp_tb");

function init() {
    const iframe = document.createElement("iframe");
    iframe.src = "/game/tuxgolf.php";
    iframe.width = 640;
    iframe.height = 480;
    iframe.id = "game";
    div.append(iframe);
    win_xp_tb.classList = "windows_xp_titlebar"
    button.classList = "hidden";
    warning.classList = "hidden";
}
function show_info() {
    const div = document.getElementById("info");
    div.classList.remove("hidden")
}
function hide_info() {
    const div = document.getElementById("info");
    div.classList.add("hidden")
}
function show_credits() {
    const div = document.getElementById("credits");
    div.classList.remove("hidden")
}
function hide_credits() {
    const div = document.getElementById("credits");
    div.classList.add("hidden")
}
function show_controls() {
    const div = document.getElementById("controls");
    div.classList.remove("hidden")
}
function hide_controls() {
    const div = document.getElementById("controls");
    div.classList.add("hidden")
}

document.getElementById("close").addEventListener("click", function() {
    document.getElementById("game").remove();
    win_xp_tb.classList = "hidden";
    button.classList = "";
    warning.classList = "fc";
})
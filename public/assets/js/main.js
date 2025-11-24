let plcholdertitle = "lsdbloχ:αlρha";
const tt=[];let ci=0;for(let e=0;e<9;e++){const t="▁▂▃▄▅▄▃▂".slice(e)+"▁▂▃▄▅▄▃▂".slice(0,e);tt.push(t)}document.addEventListener("DOMContentLoaded",(function(){setInterval((()=>{document.title=plcholdertitle+tt[ci],ci=ci=(ci+1)%tt.length}),400)}));

const codes = [
    ['Y', 'A', 'M', 'E', 'R', 'O'],
    ['S','P','E','E','N'],
    ['3','1','4','1','5','9'] // request from eden (id:2)
];

let enabled = false;
let alephenhance = false;
let curkeys = codes.map(() => 0);
let audioContext = null;
let audioBuffer = null;

if (logged_in) {
    const clock = document.getElementById("stipend_countdown");
    let last_stipend_time = last_stipend_claim * 1000
    last_stipend_time += 1000
    function padzero(number) {
        if (number < 10) {
            return "0" + String(number)
        } else {
            return String(number)
        }
    };
    function update_stipend_timer() {
        const unix = new Date(last_stipend_time);
        let hours = unix.getUTCHours();
        if (hours < 0) {
            hours += 24;
        } else if (hours >= 24) {
            hours -= 24;
        }
        hours = padzero(hours);
        const minutes = padzero(unix.getUTCMinutes());
        const seconds = padzero(unix.getUTCSeconds());
        clock.innerHTML = hours + ":" + minutes + ":" + seconds;
        last_stipend_time = last_stipend_time - 1000
        if (last_stipend_time < 0) {
            clock.innerHTML = "CLAIM"
            clock.href = "/"
            clearInterval(a);
        }
    }
    update_stipend_timer();
    var a = setInterval(() => {
        update_stipend_timer()
    }, 1000);

    const menu_button = document.getElementById('menu_button');
    const you_menu = document.getElementById('you_menu');

    menu_button.addEventListener("click", function() {
        you_menu.classList.toggle("hidden");
    })
}

async function magicpaper() {
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const response = await fetch("/assets/audio/nsoloop.ogg");
    const arrayBuffer = await response.arrayBuffer();
    const audioBuffer = await audioContext.decodeAudioData(arrayBuffer);
    const source = audioContext.createBufferSource();
    const gainnode = audioContext.createGain();
    gainnode.gain.value = 0.3;
    gainnode.connect(audioContext.destination);
    source.connect(gainnode);
    source.buffer = audioBuffer;
    source.loop = true;
    source.start(0);
    plcholdertitle = "NEEDY STREAMER OVERLOAD";
    const newstyle = document.createElement('style');
    const css = `
        body{animation:movebg calc(calc(1s/var(--bpm))*60)infinite cubic-bezier(0,.1,0,1)}.bounce{animation:bounce calc(calc(1s/var(--bpm))*60)cubic-bezier(0,.1,0,1)infinite}.jump{position:relative;animation:jump calc(calc(1s/var(--bpm))*60)cubic-bezier(0,.1,0,1)infinite}.rainbow{background:linear-gradient(to right,#00ff00,#00ffff,#ff00ff,#ffff00);background-size:400%100%;animation:tripping calc(calc(60s/var(--bpm))*2)cubic-bezier(0,.1,0,1)infinite}.rainbow{background:linear-gradient(to-right,#ef5350,#ff9800,#ffee58,#4caf50,#29b6f6,#9c27b0,#ef5350)}@keyframes bounce{0%{height:400px;rotate:0deg;}95%{height:150px;rotate:360deg;}100%{height:400px;rotate:400deg;}}@keyframes jump{0%{bottom:0;height:200px;width:200px}40%{bottom:50px;height:300px;width:200px}100%{bottom:0;height:200px;width:200px}}@keyframes tripping{0%{background-position:0%0%}100%{background-position:-300%0%}}
    `;
    newstyle.textContent = css;
    document.head.appendChild(newstyle);
    const kangel = document.getElementById('speen');
    if (kangel) { 
        kangel.src = '/assets/images/kangeldrug.png'
    }
    const lsddiv = document.createElement('div');
    lsddiv.classList = "lsd rainbow";
    document.body.prepend(lsddiv);
}



async function setupSpeen() {
    const newstyle = document.createElement('style');
    const css = `
        @keyframes speen {0% { transform: rotate(0deg); }100% { transform: rotate(1440deg); }}body.is-spinning {animation: speen 2000ms cubic-bezier(0, 0.1, 0, 1) forwards;}`;
    newstyle.textContent = css;
    document.head.appendChild(newstyle);
}

async function speen() {
    const body = document.body;
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const response = await fetch("/assets/audio/speen.ogg");
    const arrayBuffer = await response.arrayBuffer();
    const audioBuffer = await audioContext.decodeAudioData(arrayBuffer);
    const source = audioContext.createBufferSource();
    const gainnode = audioContext.createGain();
    gainnode.gain.value = 0.3;
    gainnode.connect(audioContext.destination);
    source.connect(gainnode);
    source.buffer = audioBuffer;
    source.start(0);
    body.classList.add('is-spinning');
    setTimeout(() => {
        body.classList.remove('is-spinning');
    }, 2000);
}

async function playaleph() {
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const response = await fetch("/assets/audio/aleph.opus");
    const arrayBuffer = await response.arrayBuffer();
    const audioBuffer = await audioContext.decodeAudioData(arrayBuffer);
    const source = audioContext.createBufferSource();
    const gainnode = audioContext.createGain();
    gainnode.gain.value = 0.3;
    gainnode.connect(audioContext.destination);
    source.connect(gainnode);
    source.buffer = audioBuffer;
    source.start(0);
}

function coolfunction(aa) {
    if (aa === 0) {
        if (!enabled) {magicpaper();}
        enabled = true;
    } else if (aa === 1) {
        speen();
    } else if (aa === 2) {
        if (!alephenhance) {playaleph();}
        alephenhance = true;
    }
}

window.addEventListener('keydown', (e) => {
    const key = e.key.toLowerCase();
    for (let i = 0; i < codes.length; i++) {
        const code = codes[i];
        let curkey = curkeys[i];
        const nextkey = code[curkey].toLowerCase();
        if (key === nextkey) {
            curkeys[i]++; 
        } else {
            curkeys[i] = 0;
            if (key === code[0].toLowerCase()) {
                curkeys[i] = 1;
            }
        }
        if (curkeys[i] === code.length) {
            console.log(i)
            coolfunction(i);
            curkeys[i] = 0;
        }
    }
});

setupSpeen();
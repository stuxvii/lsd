const messagedrawer = document.getElementById('messagedrawer');

const params = new URLSearchParams(window.location.search);
let page = params.get('page') || 0;
let nomorescroll = false;
let requestinflight = false;

function padzero(number) {
    if (number < 10) {
        return "0" + String(number)
    } else {
        return String(number)
    }
};

async function fetchserverdata() {
    requestinflight = true;
    try {
        const url = "/social/feed?raw=true&page=" + page;
        const request = await fetch(url, {
            method: 'GET'
        });

        const stat = request.status;

        if (request.ok) {
            const resp = await request.json();
            return resp;
        } else if (stat == 404) {
            nomorescroll = true;
            return;
        } else {
            const resp = await request.json();
            return "Unhandled error: ", resp, " With error code: ", stat;
        }
    } catch (error) {
        console.error("Gasp! An error. ", error)
        nomorescroll = true;
    }
}

async function setserverdata() {
    try {
        const serverdata = await fetchserverdata();
        page++
        if (typeof serverdata === 'object' && serverdata !== null) {
            serverdata.forEach(item => {
                const maindiv = document.createElement("div");
                const messageuploadtimediv = document.createElement("div");
                const messagediv = document.createElement("div");
                const messageboxdiv = document.createElement("div");
                const profilelink = document.createElement("a");
                const profilepic = document.createElement("img");
                const profilename = document.createElement("span");
                const reportlink = document.createElement("a");
                const permalink = document.createElement("a");
                const message = document.createElement("span");
                const messageuploadtime = document.createElement("span");

                maindiv.classList = "fr";
                maindiv.style = "width:100%;justify-content:space-between;padding:0;";

                profilelink.classList = "border";
                profilelink.href = "/social/profile?id=" + item.author;

                profilepic.src = "/social/avatar?id=" + item.author;
                profilepic.height = "100";
                profilepic.alt = item.username + "'s avatar."
                profilename.textContent = item.username;

                messageboxdiv.classList = "msgbox";
                messagediv.classList = "msg";
                message.classList = "msg";
                message.innerHTML = item.content;

                reportlink.href = "/moderation/report?type=feed&asset=" + item.id;
                reportlink.textContent = "Report";
                permalink.href = "/social/post?id=" + item.id;
                permalink.textContent = "Permalink";
                messageuploadtimediv.classList = "msgdate";

                tsseconds = item.uploadtimestamp
                tsmilliseconds = tsseconds * 1000;

                dateobj = new Date(tsmilliseconds);

                timeyear = padzero(dateobj.getFullYear());
                timemonths = padzero(dateobj.getMonth());
                timedays = padzero(dateobj.getDate());
                timehours = padzero(dateobj.getHours());
                timeminutes = padzero(dateobj.getMinutes());
                timeseconds = padzero(dateobj.getSeconds());

                fulldate = timeyear + "-" + timedays + "-" + timemonths + " " + timehours + ":" + timeminutes + ":" + timeseconds;
                
                messageuploadtime.textContent = fulldate;
                messageuploadtimediv.append(reportlink);
                messageuploadtimediv.append(permalink);
                messageuploadtimediv.append(messageuploadtime);

                profilelink.append(profilepic);
                profilelink.append(profilename);
                messagediv.append(message);
                messageboxdiv.append(messagediv);
                messageboxdiv.append(messageuploadtimediv);
                
                maindiv.append(profilelink);
                maindiv.append(messageboxdiv);

                messagedrawer.append(maindiv);
            });
            requestinflight = false;
        } else if (serverdata === undefined && !nomorescroll) {
            if (Array.isArray(serverdata) && serverdata.length === 0) {
                 nomorescroll = true;
            }
        }
        
    } catch (error) {
        console.error("???? what in the world could be happening right now oh it's just that ", error);
    }
}
setserverdata();

messagedrawer.addEventListener('scroll', function() {
    const scrolltop = messagedrawer.scrollTop;
    const clientheight = messagedrawer.scrollHeight - messagedrawer.clientHeight;
    
    if (scrolltop + 5 >= clientheight && !nomorescroll && !requestinflight) {
        setserverdata();
    }
});
const itemsdrawer = document.getElementById('itemsdrawer');
const last_page_button = document.getElementById('pastpage');
const page_indicator = document.getElementById('page_indicator');
const next_page_button = document.getElementById('nextpage');

const params = new URLSearchParams(window.location.search);
let page = Number(params.get('page')) || 0;
let category = Number(params.get('meow')) || 2;
let nomorescroll = false;
let requestinflight = false;

next_page_button.href = `https://lsdblox.cc/asset/catalog?meow=${category}&page=${page + 1}`;
last_page_button.href = `https://lsdblox.cc/asset/catalog?meow=${category}&page=${page - 1}`;
page_indicator.textContent = page - 1;
page_indicator.href = `https://lsdblox.cc/asset/catalog?meow=${category}&page=${page - 1}`;

async function fetchserverdata() {
    requestinflight = true;
    try {
        const url = "/asset/catalog?meow=" + category + "&page=" + page + "&raw=true";
        const request = await fetch(url, {
            method: 'GET'
        });

        const stat = request.status;

        if (request.ok) {
            const resp = await request.json();
            if (resp.length == 0) {
                nomorescroll = true;
                const itemprice = document.createElement("span");
                itemprice.textContent = "You've reached the end.";
                itemsdrawer.append(itemprice);
                return;
            }
            return resp;
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
                let actual_item_name = item.name;
                const length = 15;
                const trimmed_string = actual_item_name.length > length ? actual_item_name.substring(0, length - 3) + "…" : actual_item_name; // stackoverflow.com/a/32243756
                const item_div = document.createElement("div");
                const item_link = document.createElement("a");
                const item_asset_div = document.createElement("div");
                let itemasset;
                itemasset = document.createElement("img");
                itemasset.classList = "catalogitemimg";
                
                const iteminfo = document.createElement("div");
                const itemname = document.createElement("span");
                const itemprice = document.createElement("span");
                
                item_div.classList = "";
                item_link.href = `/asset/item?id=${item.id}`;
                itemname.textContent = trimmed_string;
                
                let itemvalue = item.value > 0 ? `¥${item.value}` : "Free";
                itemprice.textContent = itemvalue;
                
                iteminfo.append(itemname, itemprice);
                iteminfo.classList = "catalogiteminfo";
                item_link.classList = "catalogitem";
                itemasset.src = `/asset/thumbnail?id=${item.id}`;

                item_asset_div.classList = "catalogitemasset";
                item_asset_div.append(itemasset);
                item_link.append(item_asset_div, iteminfo);
                itemsdrawer.append(item_link);
                next_page_button.href = `https://lsdblox.cc/asset/catalog?meow=${category}&page=${page}`;
                last_page_button.href = `https://lsdblox.cc/asset/catalog?meow=${category}&page=${page - 2}`;
                page_indicator.textContent = page - 1;
                page_indicator.href = `https://lsdblox.cc/asset/catalog?meow=${category}&page=${page}`;
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

itemsdrawer.addEventListener('wheel', function() {
    const scrolltop = itemsdrawer.scrollTop;
    const clientheight = itemsdrawer.scrollHeight - itemsdrawer.clientHeight;
    const isscrollingdown = event.deltaY > 0;
    if (scrolltop + 5 >= clientheight && !nomorescroll && !requestinflight) {
        event.preventDefault();
        setserverdata();
    }
})

itemsdrawer.addEventListener('scroll', function() {
    const scrolltop = itemsdrawer.scrollTop;
    const clientheight = itemsdrawer.scrollHeight - itemsdrawer.clientHeight;
    
    if (scrolltop + 5 >= clientheight && !nomorescroll && !requestinflight) {
        setserverdata();
    }
});
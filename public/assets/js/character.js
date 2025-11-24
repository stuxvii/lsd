const colorpicker = document.getElementById("colorpicker");
const charbody = document.getElementById("char");
const character_view = document.getElementById("character_view");
const rs = document.getElementById("renderstat");
const renderimg = document.getElementById("render");
const colorpickerdiv = document.getElementById("colorpickerdiv");
const selectedBodyPart = document.getElementById("selectedBodyPart");
const switch_category_button = document.getElementById("switch_category_button");
const category_selection = document.getElementById("category_selection");
const inventory_drawer = document.getElementById("inventory_drawer");

var bodypart = "";
let pickingcolor = false;

function updcolor(event) {
    const spanel = event.target;
    if (spanel.hasAttribute("colorbrick")) {
        const color = spanel.getAttribute("style").slice(17, -1);
        const bcolor = spanel.getAttribute("colorbrick");
        document.getElementById(bodypart).setAttribute("color", bcolor);
        document.getElementById(bodypart).style.backgroundColor = color;
    }
}

colorpicker.addEventListener("mousedown", function(event) {
    pickingcolor = true;
    updcolor(event);
});

document.addEventListener("mousemove", function(event) {
    if (pickingcolor) {
        updcolor(event);
    }
});

document.addEventListener("mouseup", function() {
    pickingcolor = false;
});

charbody.addEventListener("click", function(event) {
    const spañel = event.target;
    if (spañel.nodeName == "SPAN" || spañel.closest('.bodypart')) {
        colorpickerdiv.appendChild(charbody);
        const partElement = spañel.nodeName == "SPAN" ? spañel : spañel.closest('.bodypart');
        bodypart = partElement.getAttribute("id");
        console.log(bodypart);
        colorpickerdiv.classList.remove("hidden");
        selectedBodyPart.textContent = "Selected: " + bodypart;
    }
});

function closemodal() {
    character_view.appendChild(charbody);
    colorpickerdiv.classList.add("hidden");
    save();
}

async function save() {
    const bodyparts = document.querySelectorAll('#char .bodypart');
    const formData = new FormData(); 
    formData.append('csrftoken', csrftoken);

    bodyparts.forEach(function(part) {
        const id = part.id;
        const color = part.getAttribute('color');
        
        if (id && color) {
            formData.append(id + '_color', color);
        }
    });

    try {
        const response = await fetch("/you/character/save", {
            method: 'POST',
            body: formData 
        });

        if (!response.ok) {
            console.error('Save failed:', await response.text());
        }

    } catch (error) {
        console.error('Fetch operation failed:', error);
    }
}

async function equipitem(id) {
    render();
    const formData = new FormData();
    const btn = document.getElementById(String(id));
    formData.append('equip', '1');
    formData.append('item', id);
    formData.append('csrftoken', csrftoken);

    try {
        const response = await fetch("/you/character/equip", {
            method: 'POST',
            body: formData 
        });

        if (response.ok) {
            btn.innerHTML = "Unequip"
            btn.onclick = function() { unequipitem(id); };
        } else {
            console.error('Equip failed:', await response.text());
        }

    } catch (error) {
        console.error('Fetch operation failed:', error);
    }
}

async function unequipitem(id) {
    render();
    const formData = new FormData();
    const btn = document.getElementById(String(id));
    formData.append('unequip', '1');
    formData.append('item', id);
    formData.append('csrftoken', csrftoken);

    try {
        const response = await fetch("/you/character/unequip", {
            method: 'POST',
            body: formData 
        });

        if (response.ok) {
            btn.innerHTML = "Equip"
            btn.onclick = function() { equipitem(id); };
        } else {
            console.error('Equip failed:', await response.text());
        }

    } catch (error) {
        console.error('Fetch operation failed:', error);
    }
}

async function render() {
    await save();
    rs.disabled = true;
    rs.innerHTML = "Saving...";
    try {
        rs.innerHTML = "Rendering...";
        const request = await fetch("/you/character/render", {
            method: 'GET'
        });

        const resp = await request.text();
        const stat = request.status;

        if (stat == 429) {
            rs.innerHTML = "Saved <br>(render on cooldown).";
        } else
        if (stat == 500) {
            rs.innerHTML = "Render Unavailable";
        } else
        if (request.ok) {
            renderimg.setAttribute('src', resp + "&t=" + new Date().getTime());
            console.log("Received URL is: ", resp);
            rs.innerHTML = "Done."
        } else {
            rs.innerHTML = "Error: " + stat;
            console.error("Got error code: ", stat)
            console.error("Unhandled error: ", resp)
        }
    } catch (error) {
        console.error("Gasp! An error. ", error)
        rs.innerHTML = "Error: " + error;
    } finally {
        setTimeout(() => {
            rs.innerHTML = "Redraw"
            rs.disabled = false
        }, 3000);
    }
}

async function get_inventory(id) {
    try {
        const request = await fetch("/you/character/inventory/" + String(id), {
            method: 'GET'
        });

        const stat = request.status;

        if (request.ok) {
            const item_data = await request.json();
            inventory_drawer.innerHTML = "";
            
            if (item_data.length === 0) {
                inventory_drawer.innerHTML = "You own no items of this type!";
            }
            item_data.forEach(item => {
                const catalog_item_div = document.createElement("div");
                catalog_item_div.classList.add("catalogitem");

                const catalog_item_link = document.createElement("a");
                catalog_item_link.href = "/asset/item?id=" + item.id;
                
                const catalog_item_button = document.createElement("button");
                catalog_item_button.id = item.id;
                catalog_item_button.onclick = item.equipped ? function() { unequipitem(item.id) } : function() { equipitem(item.id) };
                catalog_item_button.textContent = item.equipped ? "Unequip" : "Equip";

                const catalog_item_thumb = document.createElement("img");
                catalog_item_thumb.classList.add("itemimg");
                catalog_item_thumb.classList.add("catalogitemasset");
                catalog_item_thumb.src = "/asset/thumbnail?id=" + item.id;

                const catalog_item_name = document.createElement("span");
                catalog_item_name.textContent = item.name;

                catalog_item_link.append(catalog_item_thumb);
                catalog_item_link.append(catalog_item_name);

                catalog_item_div.append(catalog_item_link);
                catalog_item_div.append(catalog_item_button);
                inventory_drawer.append(catalog_item_div)
            });
        } else {
            console.error("Got error code: ", stat)
            console.error("Unhandled error: ", resp)
        }
    } catch (error) {
        console.error("Error. ", error)
    }
}

switch_category_button.addEventListener("click", 
    async function(x) {
        const val = category_selection.value;
        get_inventory(val);
    }
)
inventory_drawer
get_inventory(2);
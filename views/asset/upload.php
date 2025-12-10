<div class="focus hidden" id="help">
    <div class="border">
        <table>
            <tr>
                <th>Category</th>
                <th>Format</th>
                <th>Requirement</th>
                <th>Price</th>
                <th>Extra information</th>
            </tr>
            <tr>
                <th>Decal</th>
                <th>PNG/JPEG/WEBP</th>
                <th>Can be any image</th>
                <th>Free</th>
            </tr>
            <tr>
                <th>Audio</th>
                <th>MP3/OGG/WAV/OPUS</th>
                <th>Credit the creator</th>
                <th>¥100</th>
            </tr>
            <tr>
                <th>T-Shirt</th>
                <th>PNG/JPEG/WEBP</th>
                <th>Can be any image</th>
                <th>Free</th>
            </tr>
            <tr>
                <th>Shirt</th>
                <th>PNG/JPEG/WEBP</th>
                <th><a href="/assets/images/Template-Shirts-R15.png">Template</a></th>
                <th>¥10</th>
            </tr>
            <tr>
                <th>Pants</th>
                <th>PNG/JPEG/WEBP</th>
                <th><a href="/assets/images/Template-Pants-R15.png">Template</a></th>
                <th>¥10</th>
            </tr>
            <tr>
                <th>Face</th>
                <th>PNG/JPEG/WEBP</th>
                <th>Can be any image</th>
                <th>¥50</th>
            </tr>
            <tr>
                <th>Head</th>
                <th>OBJ</th>
                <th></th>
                <th>¥250</th>
                <th>Map the UVs correctly for faces!</th>
            </tr>
            <tr>
                <th>Hat</th>
                <th>OBJ</th>
                <th>Upload the texture as a decal before uploading the hat itself.</th>
                <th>¥75</th>
                <th></th>
            </tr>
            <tr>
                <th>Ad</th>
                <th>PNG/JPEG/WEBP</th>
                <th>Can be any image, preferably 300px by 300px.</th>
                <th>¥500</th>
                <th>Will last a week (7 days/604800 seconds)</th>
            </tr>
        </table>
        <span>Note that assets <b>will</b> be processed, parsed, and compressed!</span>
        <span>Also btw most prices are arbitrary. If you have any issues with 'em discuss it with me personally.</span>
        <button onclick="hidehelp()">Close</button>
    </div>
</div>
<div class="border" style="padding:15px;">
    <span id="status-message"></span>
    <form id="plrform" method="post" action="/asset/upload" enctype="multipart/form-data" class="fc aifs">
        <input type="file" id="filetoupload" name="filetoupload" required>
        <br>
        <span id="itemname_label">Name</span>
        <input type="text" placeholder="My epic asset" name="itemname" id="itemname" required>
        <br>
        <label for="itemdesc" id="itemdesc_disp">
            <span>Description</span>
            <br>
            <textarea type="textarea" placeholder="Nice shirt with alpha. Get good LSDBLOX street cred with this shirt." rows="4" cols="16" name="itemdesc" id="itemdesc"></textarea>
        </label>
        <br>
        <label for="itemprice" id="itemprice_disp">
            <span>Price</span>
            <br>
            <input type="number" placeholder="0" value="0" name="itemprice" id="itemprice" required>
        </label>
        <br>
        <label for="public" id="public_disp">
            <input type="checkbox" name="public" id="public" checked>
            Public
        </label>
        <input type="hidden" name="csrftoken" value="<?= $_SESSION['csrftoken'] ?>">
        <select id="type" name="type" style="margin-top:6px;">
            <option value="1">Decal</option>
            <option value="2">Audio</option>
            <option value="4">T-Shirt</option>
            <option value="5">Shirt</option>
            <option value="6">Pants</option>
            <option value="7">Face</option>
            <option value="8">Head</option>
            <option value="9">Hat</option>
            <option value="10">Ad</option>
        </select>
        <br>
        <label hidden id="texture_label" for="texture">
            Mesh texture
            <br>
            <input type="hidden" placeholder="Decal ID" name="texture" id="texture">
        </label>
        <input type="submit" value="Upload">
    </form>
</div>
<button onclick="showhelp()">Information</button>
<script>
    const form = document.getElementById('plrform');
    const statusMessage = document.getElementById('status-message');
    const help_modal = document.getElementById('help');

    function hidehelp() {
        help_modal.classList.add("hidden");
    }
    function showhelp() {
        help_modal.classList.remove("hidden");
    }

    document.getElementById('type').addEventListener('input', function() {
        if (document.getElementById('type').value == 9) {
            document.getElementById('texture_label').removeAttribute("hidden", false);
            document.getElementById('texture').type = "number";
        } else {
            document.getElementById('texture_label').setAttribute("hidden", true);
            document.getElementById('texture').type = "hidden";
        }
        if (document.getElementById('type').value == 10) {
            document.getElementById('itemdesc_disp').classList.add("hidden");
            document.getElementById('itemprice_disp').classList.add("hidden");
            document.getElementById('public_disp').classList.add("hidden");
            document.getElementById('itemname_label').textContent = "Link";
            document.getElementById('itemname').setAttribute('placeholder', 'https://lsdblox.cc/social/groups?id=1');
        } else {
            document.getElementById('itemdesc_disp').classList.remove("hidden");
            document.getElementById('itemprice_disp').classList.remove("hidden");
            document.getElementById('public_disp').classList.remove("hidden");
            document.getElementById('itemname_label').textContent = "Name";
            document.getElementById('itemname').setAttribute('placeholder', 'My epic asset');
        }
    })

    form.addEventListener('submit', function(event) {
        event.preventDefault();
        if ((document.getElementById('type').value == 9) && document.getElementById('texture').value == undefined) {
            if (!confirm("WARNING! YOU HAVE **NOT** SET A TEXTURE FOR THIS HAT! You may wish to select one. Press OK/Accept to continue anyways.")) {
                return;
            }
        }
        statusMessage.textContent = 'Uploading...';
        const formData = new FormData(form);
        const actionUrl = form.getAttribute('action');
        fetch(actionUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            const isOk = response.ok; 
            const status = response.status;
            return response.text().then(text => ({ 
                isOk, 
                status, 
                text 
            }));
        })
        .then(({ isOk, status, text }) => {
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                if (!isOk) {
                    throw new Error(`Server returned status ${status}. Non-JSON response: ${text.substring(0, 100)}...`);
                }
                throw new Error('Received non-JSON response from server.');
            }
            
            if (!isOk) {
                throw new Error(data.message || `Server error occurred with status ${status}.`);
            }

            return data;
        })
        .then(data => {
            if (data.status === 'success') {
                form.reset()
                statusMessage.textContent = data.message || 'Item uploaded successfully! https://lsdblox.cc/asset/item?id=' + data.assetid;
                statusMessage.style.color = 'green';
            } else {
                statusMessage.textContent = data.message || 'Upload failed with an unknown error.';
                statusMessage.style.color = 'orange';
                console.log(data);
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            statusMessage.textContent = 'Upload Failed: ' + error.message;
            statusMessage.style.color = 'red';
        });
    });
</script>
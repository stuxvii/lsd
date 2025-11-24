<div class="focus hidden" id="help">
    <div class="border">
        <table>
            <tr>
                <th></th>
                <th>T-Shirt</th>
                <th>Decal</th>
                <th>Shirt</th>
                <th>Pants</th>
                <th>Audio</th>
                <th>Mesh</th>
            </tr>
            <tr>
                <td>Format</td>
                <td>PNG-JPEG-WEBP</td>
                <td>PNG-JPEG-WEBP</td>
                <td>PNG-JPEG-WEBP</td>
                <td>PNG-JPEG-WEBP</td>
                <td>MP3-WAV-OGG-OPUS</td>
                <td>Plain text</td>
            </tr>
            <tr>
                <td>Requirement</td>
                <td>Can be any image</td>
                <td>Can be any image</td>
                <td><a href="/images/Template-Shirts-R15.png">Template</a></td>
                <td><a href="/images/Template-Pants-R15.png">Template</a></td>
                <td>Credit the creator</td>
                <td>Must be V1.0 <a href="https://bitl.itch.io/novetus">OBJ2MESH Converter</a></td>
            </tr>
            <tr>
                <td>Price</td>
                <td>Free</td>
                <td>Free</td>
                <td>¥10</td>
                <td>¥10</td>
                <td>¥100</td>
                <td>¥5</td>
            </tr>
        </table>
        <span>Note that assets <b>will</b> be processed, parsed, and compressed!</span>
        <button onclick="hidehelp()">Close</button>
    </div>
</div>
<div class="border" style="padding:15px;">
    <span id="status-message"></span>
    <form id="plrform" method="post" action="/asset/upload" enctype="multipart/form-data" class="fc aifs">
        <input type="file" id="filetoupload" name="filetoupload" required>
        <br>
        <span>Name</span>
        <input type="text" placeholder="My epic asset" name="itemname" id="itemname" required>
        <br>
        Description
        <br>
        <textarea type="textarea" placeholder="Nice shirt with alpha. Get good LSDBLOX street cred with this shirt." rows="4" cols="16" name="itemdesc" id="itemdesc"></textarea>
        <br>
        Price
        <br>
        <input type="number" placeholder="0" value="0" name="itemprice" id="itemprice" required>
        <br>
        <label for="public">
            <input type="checkbox" name="public" id="public" checked>
            Public
        </label>
        <input type="hidden" name="csrftoken" value="<?=$_SESSION["csrftoken"]?>">
        <select id="type" name="type" style="margin-top:6px;">
            <option value="1">Decal</option>
            <option value="2">Audio</option>
            <option value="4">T-Shirt</option>
            <option value="5">Shirt</option>
            <option value="6">Pants</option>
            <option value="7">Face (Image)</option>
            <?php if ($this->user_info["isoperator"]): ?>
            <option value="8">Head (OBJ)</option>
            <option value="9">Hat (OBJ)</option>
            <?php endif; ?>
        </select>
        <br>
        <?php if ($this->user_info["isoperator"]): ?>
        <label hidden id="hat_texture_label" for="hat_texture">
            Hat texture (Decal)
            <br>
            <input type="hidden" placeholder="34" name="hat_texture" id="hat_texture" required>
        </label>
        <?php endif; ?>
        <input type="submit" value="Upload">
    </form>
</div>
<button onclick="showhelp()">Show help</button>
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
        console.log(document.getElementById('type').value );
        if (document.getElementById('type').value == 9) {
            document.getElementById('hat_texture_label').removeAttribute("hidden", false);
            document.getElementById('hat_texture').type = "number";
        } else {
            document.getElementById('hat_texture_label').setAttribute("hidden", true);
            document.getElementById('hat_texture').type = "hidden";
        }
    })

    form.addEventListener('submit', function(event) {
        event.preventDefault();
        if (document.getElementById('type').value == 9 && document.getElementById('hat_texture').value == undefined) {
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
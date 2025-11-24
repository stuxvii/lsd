function promptmanage(itemId) {
    const container = document.getElementById('manage');
    if (!itemId) {
        console.error("Item ID not found for management thing. Weirdddd.");
        return;
    }
    this.disabled = true; // once manage panel is open, we dont want to spawn any new panels
    const postData = new URLSearchParams();
    postData.append('id', itemId);
    postData.append('csrftoken', csrftoken);

    fetch('/asset/item', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: postData
    })
    .then(response => {
        return response.json().then(data => {
            if (!response.ok) {
                throw new Error(data.message || 'Server error occurred.'); 
            }
            return data;
        });
    })
    .then(data => {
        if (data.status === 'success') {
            container.className = "focus";
            container.innerHTML = data.message;
            console.log(data.message);
        } else {
            container.textContent = data.message;
            console.log('Auth failure:', data);
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        container.textContent = 'Connection or Server Failure: ' + error.message;
        container.style.color = 'red';
    })
};

function setimagemain(itemId) {
    if (!itemId) {
        console.error("Item ID not found for purchase.");
        return;
    }
    this.disabled = true;
    const postData = new URLSearchParams();
    postData.append('itemid', itemId);

    fetch('/', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: postData
    })
    .then(response => {
        return response.json().then(data => {
            if (!response.ok) {
                throw new Error(data.message || 'Server error occurred.'); 
            }
            return data;
        });
    })
    .then(data => {
        if (data.status === 'success') {
            location.href = "/";
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
    })
    .finally(() => {
        this.disabled = false;
    });
};
function purchase(itemId) {
    const statusMessage = document.getElementById('purchase-status-message');
    const amountPesos = document.getElementById('amountofmoney');
    if (!itemId) {
        console.error("Item ID not found for purchase.");
        return;
    }
    statusMessage.style.color = null;
    statusMessage.textContent = `Processing...`;
    this.disabled = true;
    const postData = new URLSearchParams();
    postData.append('itemid', itemId);
    postData.append('csrftoken', csrftoken);

    fetch('/asset/purchase', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: postData
    })
    .then(response => {
        return response.json().then(data => {
            if (!response.ok) {
                throw new Error(data.message || 'Server error occurred.'); 
            }
            return data;
        });
    })
    .then(data => {
        if (data.status === 'success') {
            statusMessage.textContent = data.message || `Item ${itemId} purchased successfully!`;
            amountPesos.textContent = data.newmoney;
            statusMessage.style.color = 'green';
        } else {
            statusMessage.textContent = data.message || 'Purchase failed with an unknown error. You have not been charged.';
            statusMessage.style.color = 'red';
            console.log('Purchase Failure Data:', data);
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        statusMessage.textContent = 'Connection or Server Failure: ' + error.message;
        statusMessage.style.color = 'red';
    })
    .finally(() => {
        this.disabled = false;
    });
};
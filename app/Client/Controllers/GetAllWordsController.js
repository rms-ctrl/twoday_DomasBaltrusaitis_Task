

async function getAllWords() {
    try {
        fetch('http://localhost:8000/words', {
            method: 'GET',
        }).then(response => response.json())
            .then(data => append_json_data(data))
    } catch (e) {
        console.error(e);
    }
}

function append_json_data(data){
    console.log(data)
    var table = document.getElementById('listTable');
    data.forEach((object) => {
        var tr = document.createElement('tr');
        tr.innerHTML =
            '<td>' + object.id + '</td>' +
            '<td>' + object.text + '</td>' +
            '<td>' + `<button class="btn delbtn" onclick="toggleDelete(this, ${object.id})">Delete</button>` + '</td>' +
            '<td>' + `<button class="btn updtbtn" onclick="toggleUpdate('${object.id}', '${object.text}')">Update</button>` + '</td>'
        table.appendChild(tr);
    })
}


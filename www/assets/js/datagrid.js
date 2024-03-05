import $ from "jquery";

const createResizableTable = function(table) {
    let urlResize = table.dataset.resizeUrl;
    const cols = table.querySelectorAll('th[data-resizable-column-id]');
    [].forEach.call(cols, function(col) {
        // Add a resizer element to the column
        const resizer = document.createElement('div');
        resizer.classList.add('resizer');

        // Set the height
        resizer.style.height = `${col.offsetHeight}px`;

        col.appendChild(resizer);

        createResizableColumn(urlResize, col, resizer);
    });
};

const createResizableColumn = function(urlResize, col, resizer) {
    let x = 0;
    let w = 0;

    const mouseDownHandler = function(e) {
        if (e.button !== 0) {
            return;
        }
        x = e.clientX;

        w = parseInt(col.style.width, 10);
        if (!w) {
            w = $(col)[0].getBoundingClientRect().width;
            $(col)[0].style.width = `${w}px`;
            $(col)[0].style.maxWidth = `${w}px`;
            $(col)[0].style.minWidth = `${w}px`;
        }

        document.addEventListener('mousemove', mouseMoveHandler);
        document.addEventListener('mouseup', mouseUpHandler);

        resizer.classList.add('resizing');
    };

    const mouseMoveHandler = function(e) {
        const dx = e.clientX - x;
        col.style.width = `${w + dx}px`;
        col.style.maxWidth = `${w + dx}px`;
        col.style.minWidth = `${w + dx}px`;
    };

    const mouseUpHandler = function() {
        resizer.classList.remove('resizing');
        document.removeEventListener('mousemove', mouseMoveHandler);
        document.removeEventListener('mouseup', mouseUpHandler);
        let column = col.dataset.resizableColumnId;
        let width = parseInt(col.style.width, 10);
        dataGridRegisterAjaxCall({
            type: 'POST',
            url: urlResize,
            data: {
                column: column,
                width: width
            },
            error: function(jqXHR, textStatus, errorThrown) {
                return alert(jqXHR.statusText);
            }
        });
    };

    resizer.addEventListener('mousedown', mouseDownHandler);
};

const initResizeDatagrid = function() {
    $('.datagrid .modal').each(function(index, item) {
        let v = $(item).parent().clone();
        $(item).parent().remove();
        $('body').append(v);
    });
    $('.datagrid table').each(function(index, item) {
        createResizableTable(item);
    });
    let sortableTable = $( "#sortable-table" );
    sortableTable.sortable({
        placeholder: "ui-state-highlight",
        items: '> tbody > tr',
        update: function( event, ui ) {
            const list = event.target.querySelectorAll('tr.ui-sortable-handle');
            let i = 1;
            [].forEach.call(list, function(row) {
                const td = row.querySelector('td');
                const span = td.querySelector('span');
                const input = td.querySelector('input');
                span.innerHTML = i + '.';
                input.value = i;
                i++;
            });
        }
    });
    sortableTable.disableSelection();
};

window.initResizeDatagrid = initResizeDatagrid;

document.addEventListener('DOMContentLoaded', function() {
    initResizeDatagrid();
});
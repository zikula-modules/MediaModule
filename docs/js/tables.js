var tables = document.getElementsByTagName('table');
for(var i = 0; i < tables.length; i++) {
    tables[i].className += "mdl-data-table mdl-js-data-table";
}
var tds = document.getElementsByTagName('td');
for(i = 0; i < tds.length; i++) {
    tds[i].className += "mdl-data-table__cell--non-numeric";
}
var ths = document.getElementsByTagName('th');
for(i = 0; i < ths.length; i++) {
    ths[i].className += "mdl-data-table__cell--non-numeric";
}

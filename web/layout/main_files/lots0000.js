function search(){
	prevParams = '&lot=' + $F('lot');
	$('error').style.display = 'none';
	$('wait').style.display = 'block';	
    var myAjax = new Ajax.Request('/ajax/all/', {
        method: 'post',
        parameters: prevParams,
        onComplete: searchReturn
    });
}
function hideErr() {
	$('error').style.display = 'none';
}
function searchReturn(res){
    eval("var data=" + res.responseText);
    var href = '';
	k = data.lres;
	if (k != 'error') href = k;
    if (href != '') {
		$('wait').style.display = 'none';
		$('error').style.display = 'none';
		document.location.href = 'http://www.kre.ru'+href;  
    }
    else {
		$('wait').style.display = 'none';
		$('error').style.display = '';	
		setTimeout("hideErr()",7000); 
    }    
}
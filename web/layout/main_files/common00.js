function prnt(){
var newurl=document.location.pathname+(document.location.pathname.charAt(document.location.pathname.length-1)=='/'?'':'/')+'print/';
document.location.href=newurl;
}

function imgPopup(url,width,height){
  var left=Math.round((screen.availWidth-width)/2);
  var top=Math.round((screen.availHeight-height)/2);
  window.open('/viewimage/?i='+url,"","toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, width="+width+", height="+height+", left="+left+",top="+top);
}

function openCalc(sum) {
	var left=Math.round((screen.availWidth-450)/2);
	var top=Math.round((screen.availHeight-400)/2);
	window.open('/offers/calc/?s='+sum,"calc","toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, width="+450+", height="+400+", left="+left+",top="+top);
}
function waypoint(obj) {
	type = document.getElementById('sel_id1_v').value;
	act = document.getElementById('sel_id2_v').value;
      switch (act) {
    	case "0": //������
      		switch (type) {
        		case "0": //������� ��������
          		obj.action='/offers/1/';
          	break;
        		case "1": //������� �����������
		          obj.action='/offers/2/';
          	break;
        		case "2": //��������
		          obj.action='/offers/3/';
          	break;
        		case "3": //���������� ���
		          obj.action='/offers/5/';
          	break;
        		case "4": //������������ ������������
		          obj.action='/offers/6/';
          	break;                                            
         	}	      	
     	break;
	    case "1": //�������
	    case "2": //�����
		      obj.action='/claim/';
	    break;
		case "3": //�����
      		switch (type) {
        		case "0": //��������
          		obj.action='/offers/4/';
          	break;
        		case "1": //������������ ������������
		          obj.action='/offers/7/';
          	break;                                        
         	}	          
		break;
    	}
    obj.submit();
}

jQuery(document).ready(function() {
	jQuery('#cboxPrevious').attr('title', '���������� �����������');
	jQuery('#cboxNext').attr('title', '��������� �����������');
});
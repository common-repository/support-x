<?php
  if ( ! defined( 'ABSPATH' ) ) {
     exit;
 } 
?>
<style type="text/css">
.vx_user_img{
    display: inline-block;
    width: 40px;
    height: 40px;
    vertical-align: middle;
    border: 1px solid #ccc;
    border-radius: 50%;
    overflow: hidden;
}
.vx_ticket_box{
    border: 1px solid #b3b2b2;
    margin: 20px 0;
   /*   box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);*/
}
.vx_ticket_title{
    font-weight: bold;
    margin-left: 6px;
}
.vx_ticket_box_body ol , .vx_ticket_box_body ul{
    padding-left: 20px;
}
.vx_ticket_heading{
    font-size: 18px;
    line-height: 60px;
    font-weight: bold;
    padding-right: 24px;
    color: #666;
}
.vx_title_small{
  font-size: 14px;
  line-height: 42px;
  font-weight: bold;
  text-decoration: underline;  
}
.vx_attachment{
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 4px 10px;
}
.vx_ticket_box_title , .vx_ticket_box_footer{
    background: #f1f1f1;
    padding: 6px 20px;
}
.vx_box_reply.vx_ticket_box{  
border-left-color: #00ad5c; 
border-left-width: 4px; 
}
.vx_box_reply_me.vx_ticket_box{  
border-left-color: #f89406; 
border-left-width: 4px; 
}

.vx_box_time{
    line-height: 40px;
    float: right;
}
.vx_ticket_box_body{
    padding: 10px;
    background-color: #fff;
}
.vx_user_img img{
    width: 100%;
}
  .vx_field_label{
    margin: 4px 0;
    display: block;
    font-weight: bold;
}
.vx_form_control{
    margin-bottom: 20px;
}
.vx_field_label .required{
    color: #ff2f2f;
}
.vx_input{width: 98%; padding: 6px; }

.vx_alert_msg
{
    margin: 20px 0;
    padding: 12px;
    border-style: solid;
    border-width: 0;
    border-left-width: 3px;
}

.vx_alert_msg_success
{
background-color: #eff7ed;
     border-color: #64b450;
}

.vx_alert_msg_error
{
    color: #b94a48;
background-color: #f2dede;
  border-color: #b94a48;
   /* background-color: #fcf8f2;
    border-color: #f0ad4e;*/
}

.vx_badge {
  padding: 1px 9px 2px;
  font-size: 12.025px;
  font-weight: bold;
  white-space: nowrap;
  color: #ffffff;
  background-color: #999999;
  -webkit-border-radius: 9px;
  -moz-border-radius: 9px;
  border-radius: 9px;
}

.vx_badge_error {
  background-color: #b94a48;
}

.vx_badge_pending {
  background-color: #f89406;
}


.vx_badge_active {
  background-color: #468847;
}

.vx_badge_info {
  background-color: #3a87ad;
}

.vx_paging_ul{
    margin: 0;
    padding: 0;
    list-style-type: none;
}
.vx_paging_ul li{
display: inline-block;
vertical-align: baseline;

       width: 40px;
       margin: 0 4px;
       background-color: #f1f1f1;
}
.vx_paging_ul li a , .vx_paging_span{
    font-weight: bold;
display: block;
    padding: 6px;
    text-align: center;
}
.vx_paging_ul li.vx_active_page_link a , .vx_paging_ul li a:hover{
   background: #fff;
   border: 2px solid #ddd; 
}
.vx_ticket_box_title span{
    display: inline-block;
}
.vx_checkbox{
    vertical-align: middle;
    margin-left: 6px;
}
.vx_entries_table{
    width: 100%;
}
.vx_entries_table .vx_td_center{
    text-align: center;
}
.vx_entries_table th{
    border-bottom: 2px solid #bbb;
    padding: 8px 6px;
    text-align: center;
}
.vx_entries_table tr{
    border-bottom: 1px solid #eee;
}
.vx_entries_table tr td{
padding: 14px 8px;
 border-bottom: 1px solid #bbb;;
}


.vx_input{
      height: 48px;  
            -webkit-transition: all .5s ease-in-out;
    -moz-transition: all .5s ease-in-out;
    -ms-transition: all .5s ease-in-out;
    -o-transition: all .5s ease-in-out;
    transition: all .5s ease-in-out;
    -webkit-border-radius: 0px;
    -moz-border-radius: 0px;
    -ms-border-radius: 0px;
    -o-border-radius: 0px;
    border-radius: 0px;

             border-width: 1px;
        border-style: solid;
        border-color: #cfcfcf;

    position: relative;
    vertical-align: top;
    display: block;
    float: none;
    outline: 0;
  
  box-shadow: none;   background: rgba(246, 246, 246, 1);
      padding-left: 8px; 
  padding-right: 8px;
   padding-top:0px;
padding-bottom:0px;

  font-size: 16px;
   border-radius: 2px;
  color: rgba(84, 84, 84, 1);
    width: 100%;
*zoom:1
    }
   
.vx_input::placeholder{
    color: rgba(156, 156, 156, 1);  
 }
.vx_input:hover{
      border-color: #53464a;    
      }
.vx_input:focus{
             border-style: solid;
  background: rgba(137, 124, 128, 0.17);
        border-color:#53464a;
      color: rgba(84, 84, 84, 1);

    outline: 0;   
  }
  textarea.vx_input{
      height: 120px;
      padding-top: 6px;
  }
  .vx_support_submit_btn{
         background-color: rgba(107, 94, 98, 1); 
     font-size:18px;
     color:rgba(255, 255, 255, 1);
     cursor:pointer;  
     font-style: normal; font-weight: normal; 
  border-width:0px;  
  border-color:#6b5e62;  
  border-style:solid;  text-align:center;
    }
 .vx_support_submit_btn:hover, .vx_support_submit_btn:focus {
    background-color:rgba(83, 70, 74, 1);
    border-color:#53464a;
}
 .vx_support_submit_btn:disabled {
    cursor: wait;
    opacity:.7;
}


    </style>

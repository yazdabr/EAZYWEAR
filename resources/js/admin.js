/* ===========================
        USER DROPDOWN
=========================== */

const userBtn=document.querySelector(".user-btn");

const dropdown=document.querySelector(".dropdown-menu");

if(userBtn){

    userBtn.onclick=()=>{

        dropdown.classList.toggle("show");

    }

}

window.onclick=function(e){

    if(!e.target.closest(".user-dropdown")){

        dropdown.classList.remove("show");

    }

}
(()=>{"use strict";var e={744:(e,t,n)=>{var o=n(795);t.H=o.createRoot,o.hydrateRoot},795:e=>{e.exports=window.ReactDOM}},t={};window.copyBPlAdminShortcode=e=>{var t=document.querySelector("#bPlAdminShortcode-"+e+" input"),n=document.querySelector("#bPlAdminShortcode-"+e+" .tooltip");t.select(),t.setSelectionRange(0,30),document.execCommand("copy"),n.innerHTML=wp.i18n.__("Copied Successfully!","countdown-time"),setTimeout((()=>{n.innerHTML=wp.i18n.__("Copy To Clipboard","countdown-time")}),1500)};const n=window.React;var o=function n(o){var a=t[o];if(void 0!==a)return a.exports;var r=t[o]={exports:{}};return e[o](r,r.exports,n),r.exports}(744);document.addEventListener("DOMContentLoaded",(()=>{const e=document.querySelector(".bplAdminHelpPage");e&&(0,o.H)(e).render((0,n.createElement)("div",{className:"bplContainer"},(0,n.createElement)("div",{className:"header box"},(0,n.createElement)("h1",{className:"heading"},"Helpful Links")),(0,n.createElement)("div",{className:"body"},(0,n.createElement)("div",{className:"features col-3 col-tab-2 col-mob-1"},[{title:"Need any Assistance?",description:"Our Expert Support Team is always ready to help you out promptly.",iconClass:"fa fa-life-ring",link:"https://bplugins.com/support",linkText:"Contact Support"},{title:"Looking for Documentation?",description:"We have detailed documentation on every aspects of the plugin.",iconClass:"fa fa-file-text",link:"https://bplugins.com/docs/button-block",linkText:"Documentation"},{title:"Liked This Plugin?",description:"Glad to know that, you can support us by leaving a 5 &#11088; rating.",iconClass:"fa fa-thumbs-up",link:"https://wordpress.org/support/plugin/button-block/reviews/#new-post",linkText:"Rate the Plugin"}].map(((e,t)=>(0,n.createElement)(a,{key:t,feature:e})))))))}));const a=({feature:e})=>{const{title:t,description:o,iconClass:a,link:r,linkText:i}=e;return(0,n.createElement)("div",{className:"feature box"},(0,n.createElement)("i",{className:a}),(0,n.createElement)("h3",{dangerouslySetInnerHTML:{__html:t}}),(0,n.createElement)("p",{dangerouslySetInnerHTML:{__html:o}}),(0,n.createElement)("a",{href:r,target:"_blank",rel:"noreferrer",className:"button button-primary",dangerouslySetInnerHTML:{__html:i}}))}})();
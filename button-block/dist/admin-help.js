!function(){"use strict";var e={n:function(t){var n=t&&t.__esModule?function(){return t.default}:function(){return t};return e.d(n,{a:n}),n},d:function(t,n){for(var a in n)e.o(n,a)&&!e.o(t,a)&&Object.defineProperty(t,a,{enumerable:!0,get:n[a]})},o:function(e,t){return Object.prototype.hasOwnProperty.call(e,t)}},t=ReactDOM,n=e.n(t);document.addEventListener("DOMContentLoaded",(function(){var e=document.querySelector(".bplAdminHelpPage");n().render(React.createElement("div",{className:"bplContainer"},React.createElement("div",{className:"header box"},React.createElement("h1",{className:"heading"},"Helpful Links")),React.createElement("div",{className:"body"},React.createElement("div",{className:"features col-3 col-tab-2 col-mob-1"},[{title:"Need any Assistance?",description:"Our Expert Support Team is always ready to help you out promptly.",iconClass:"fa fa-life-ring",link:"https://bplugins.com/support",linkText:"Contact Support"},{title:"Looking for Documentation?",description:"We have detailed documentation on every aspects of the plugin.",iconClass:"fa fa-file-text",link:"https://bplugins.com/docs/button-block",linkText:"Documentation"},{title:"Liked This Plugin?",description:"Glad to know that, you can support us by leaving a 5 &#11088; rating.",iconClass:"fa fa-thumbs-up",link:"https://wordpress.org/support/plugin/button-block/reviews/#new-post",linkText:"Rate the Plugin"}].map((function(e,t){return React.createElement(a,{key:t,feature:e})}))))),e)}));var a=function(e){var t=e.feature,n=t.title,a=t.description,r=t.iconClass,o=t.link,l=t.linkText;return React.createElement("div",{className:"feature box"},React.createElement("i",{className:r}),React.createElement("h3",{dangerouslySetInnerHTML:{__html:n}}),React.createElement("p",{dangerouslySetInnerHTML:{__html:a}}),React.createElement("a",{href:o,target:"_blank",rel:"noreferrer",className:"button button-primary",dangerouslySetInnerHTML:{__html:l}}))}}();
//# sourceMappingURL=admin-help.js.map
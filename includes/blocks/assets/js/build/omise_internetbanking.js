(()=>{"use strict";const e=window.React,t=window.wp.element,n=window.wp.i18n,a=window.wp.htmlEntities,s=window.wc.wcBlocksRegistry,l=(0,window.wc.wcSettings.getSetting)("omise_internetbanking_data",{}),i=(0,a.decodeEntities)(l.title)||"No title set",r=s=>{const{eventRegistration:i,emitResponse:r}=s,{onPaymentSetup:m}=i,o=(0,a.decodeEntities)(l.description||""),[c,b]=(0,t.useState)(null),p=e=>{b(e.target.value)};return(0,t.useEffect)((()=>{const e=m((async()=>{if(!c)return{type:r.responseTypes.ERROR,message:"Select a bank"};try{return{type:r.responseTypes.SUCCESS,meta:{paymentMethodData:{"omise-offsite":c}}}}catch(e){return{type:r.responseTypes.ERROR,message:e.message}}}));return()=>e()}),[r.responseTypes.ERROR,r.responseTypes.SUCCESS,m,c]),(0,e.createElement)(e.Fragment,null,o&&(0,e.createElement)("p",null,o),(0,e.createElement)("fieldset",{id:"omise-form-internetbanking"},(0,e.createElement)("ul",{className:"omise-banks-list"},(0,e.createElement)("li",{className:"item"},(0,e.createElement)("input",{id:"internet_banking_bay",type:"radio",name:"omise-offsite",value:"internet_banking_bay",onChange:p}),(0,e.createElement)("label",{htmlFor:"internet_banking_bay"},(0,e.createElement)("div",{className:"bank-logo bay"}),(0,e.createElement)("div",{className:"bank-label"},(0,e.createElement)("span",{className:"title"},(0,n.__)("Krungsri Bank","omise")),(0,e.createElement)("br",null),(0,e.createElement)("span",{className:"omise-secondary-text"},(0,n.__)("Fee: 15 THB (same zone), 15 THB (out zone)","omise"))))),(0,e.createElement)("li",{className:"item"},(0,e.createElement)("input",{id:"internet_banking_bbl",type:"radio",name:"omise-offsite",value:"internet_banking_bbl",onChange:p}),(0,e.createElement)("label",{htmlFor:"internet_banking_bbl"},(0,e.createElement)("div",{className:"bank-logo bbl"}),(0,e.createElement)("div",{className:"bank-label"},(0,e.createElement)("span",{className:"title"},(0,n.__)("Bangkok Bank","omise")),(0,e.createElement)("br",null),(0,e.createElement)("span",{className:"omise-secondary-text"},(0,n.__)("Fee: 10 THB (same zone), 20 THB (out zone)","omise"))))))))};(0,s.registerPaymentMethod)({name:l.name||"",label:(0,e.createElement)((t=>{const{PaymentMethodLabel:n}=t.components;return(0,e.createElement)(n,{text:i})}),null),content:(0,e.createElement)(r,null),edit:(0,e.createElement)(r,null),canMakePayment:()=>!0,ariaLabel:i,supports:{features:l.supports}})})();
(()=>{"use strict";const e=window.React,t=window.wp.element,n=window.wp.i18n,a=window.wp.htmlEntities,l=window.wc.wcBlocksRegistry,r=(0,window.wc.wcSettings.getSetting)("omise_installment_data",{}),s=(0,a.decodeEntities)(r.title)||"No title set",m=l=>{const{eventRegistration:s,emitResponse:m}=l,{onPaymentSetup:i}=s,c=(0,a.decodeEntities)(r.description||""),{installment_backends:o,is_zero_interest:u}=r.data,p=(0,n.__)("Purchase Amount is lower than the monthly minimum payment amount.","omise"),_=(0,t.useRef)(null),d=(0,t.useRef)(null),E=e=>{_.current=e.target.value,d.current=null},y=e=>{d.current=e.target.value};return(0,t.useEffect)((()=>{const e=i((async()=>{if(!_.current||!d.current)return{type:m.responseTypes.ERROR,message:"Select a bank and term"};try{return{type:m.responseTypes.SUCCESS,meta:{paymentMethodData:{source:_.current,[`${_.current}_installment_terms`]:d.current}}}}catch(e){return{type:m.responseTypes.ERROR,message:e.message}}}));return()=>e()}),[i]),(0,e.createElement)(e.Fragment,null,c&&(0,e.createElement)("p",null,c),0==o.length?(0,e.createElement)("p",null,p):(0,e.createElement)("fieldset",{id:"omise-form-installment"},(0,e.createElement)("ul",{className:"omise-banks-list"},o.map(((t,a)=>(0,e.createElement)("li",{key:t._id+a,className:"item"},(0,e.createElement)("input",{id:t._id,type:"radio",name:"source[type]",value:t._id,onChange:E}),(0,e.createElement)("label",{htmlFor:t._id},(0,e.createElement)("div",{className:`bank-logo ${t.provider_code}`}),(0,e.createElement)("div",{className:"bank-label"},(0,e.createElement)("span",{className:"title"},t.provider_name),(0,e.createElement)("br",null),(0,e.createElement)("select",{id:`${t._id}_installment_terms`,name:`${t._id}_installment_terms`,className:"installment-term-select-box",onChange:y},(0,e.createElement)("option",null,"Select term"),t.available_plans.map(((t,a)=>(0,e.createElement)("option",{key:`${t.term_length}_${t.monthly_amount}_${a}`,value:t.term_length},(0,n.__)(`${t.term_length} months`,"omise"),(0,e.createElement)(e.Fragment,null," "),"(",(0,n.__)(`${t.monthly_amount} / months`,"omise"),")")))),u&&(0,e.createElement)(e.Fragment,null,(0,e.createElement)("br",null),(0,e.createElement)("span",{className:"omise-installment-interest-rate"},(0,n.__)(`( interest ${t.interest_rate} )`,"omise")))))))))))};(0,l.registerPaymentMethod)({name:r.name,label:(0,e.createElement)((t=>{const{PaymentMethodLabel:n}=t.components;return(0,e.createElement)(n,{text:s})}),null),content:(0,e.createElement)(m,null),edit:(0,e.createElement)(m,null),canMakePayment:()=>!0,ariaLabel:s,supports:{features:r.supports}})})();
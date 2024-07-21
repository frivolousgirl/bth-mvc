// import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import "./styles/app.css";
//import './styles/project/app.css';
import "./js/script.js";

if (window.location.pathname.includes('/proj')) {
    //import('./styles/project/app.css');
}


console.log("This log comes from assets/app.js - welcome to AssetMapper! 🎉");

import hello from "./js/hello.js";

console.log(hello());

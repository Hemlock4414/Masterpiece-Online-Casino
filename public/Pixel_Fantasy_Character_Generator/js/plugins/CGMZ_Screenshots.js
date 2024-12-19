/*:
 * @author Casper Gaming
 * @url https://www.caspergaming.com/plugins/cgmz/screenshots/
 * @target MZ
 * @base CGMZ_Core
 * @orderAfter CGMZ_Core
 * @plugindesc Allows you to take screenshots within the game
 * @help
 * ============================================================================
 * For terms and conditions using this plugin in your game please visit:
 * https://www.caspergaming.com/terms-of-use/
 * ============================================================================
 * Become a Patron to get access to beta/alpha plugins plus other goodies!
 * https://www.patreon.com/CasperGamingRPGM
 * ============================================================================
 * Version: 1.0.1
 * ----------------------------------------------------------------------------
 * Compatibility: Only tested with my CGMZ plugins.
 * Made for RPG Maker MZ 1.4.4
 * ----------------------------------------------------------------------------
 * Description: Lets you take screenshots within the game. Screenshots can
 * capture the game screen and be saved directly to computer or prompted to
 * save at the user's choice location.
 * ----------------------------------------------------------------------------
 * Documentation:
 * The Screenshots folder will automatically be created if it does not exist.
 * For web-hosted games, the game will prompt the user to download the
 * screenshot.
 * ----------------------Background Color Parameter----------------------------
 * By default, some things rendered by the engine are somewhat transparent.
 * Also by default, the HTML of the page has a background color of black. This
 * bg color is not captured by the screenshot as the screenshot captures only
 * what is rendered by the engine. You can add this bg color back in using the
 * background color parameter. If you're not sure, it is suggested to leave it
 * as "black". Set to blank if you want to preserve the transparency.
 * -------------------------Version History------------------------------------
 * Version 1.0.0 - Initial Release
 *
 * Version 1.0.1:
 * - Removed Filesystem Functions (moved to CGMZ Core 1.7.0+)
 *
 * @command Take Screenshot
 * @desc Takes a screenshot of the current game screen
 *
 * @param Automatic Screenshot
 * @type boolean
 * @default true
 * @desc Automatically takes a screenshot of the screen when pressing Print Screen.
 *
 * @param Screenshot Folder
 * @default screenshots
 * @desc The folder (from game project folder root) to save screenshots. Will be created automatically
 *
 * @param Background Color
 * @default black
 * @desc Background color of the screenshot. See documentation.
*/
const _0x33d6b7=_0x5edc;(function(_0x490eb8,_0x320c89){const _0x5c19bb=_0x5edc,_0x5069a7=_0x490eb8();while(!![]){try{const _0x32b330=parseInt(_0x5c19bb(0xb8))/0x1*(parseInt(_0x5c19bb(0xce))/0x2)+-parseInt(_0x5c19bb(0xbd))/0x3+-parseInt(_0x5c19bb(0xaf))/0x4*(-parseInt(_0x5c19bb(0xb4))/0x5)+parseInt(_0x5c19bb(0xd1))/0x6*(-parseInt(_0x5c19bb(0xb6))/0x7)+parseInt(_0x5c19bb(0xcb))/0x8+parseInt(_0x5c19bb(0xe1))/0x9*(parseInt(_0x5c19bb(0xde))/0xa)+-parseInt(_0x5c19bb(0xcd))/0xb*(-parseInt(_0x5c19bb(0xca))/0xc);if(_0x32b330===_0x320c89)break;else _0x5069a7['push'](_0x5069a7['shift']());}catch(_0x2ee199){_0x5069a7['push'](_0x5069a7['shift']());}}}(_0x1c6d,0x3f1bf));var Imported=Imported||{};Imported[_0x33d6b7(0xc0)]=!![];function _0x5edc(_0x246818,_0x2e84fb){const _0x1c6de4=_0x1c6d();return _0x5edc=function(_0x5edc1f,_0x27505c){_0x5edc1f=_0x5edc1f-0xad;let _0x250a3d=_0x1c6de4[_0x5edc1f];return _0x250a3d;},_0x5edc(_0x246818,_0x2e84fb);}var CGMZ=CGMZ||{};CGMZ[_0x33d6b7(0xb2)]=CGMZ[_0x33d6b7(0xb2)]||{},CGMZ[_0x33d6b7(0xb2)]['Screenshots']=_0x33d6b7(0xed),CGMZ['Screenshots']=CGMZ[_0x33d6b7(0xd6)]||{},CGMZ[_0x33d6b7(0xd6)][_0x33d6b7(0xc7)]=PluginManager[_0x33d6b7(0xc7)](_0x33d6b7(0xc0)),CGMZ[_0x33d6b7(0xd6)][_0x33d6b7(0xe8)]=CGMZ[_0x33d6b7(0xd6)][_0x33d6b7(0xc7)]['Automatic\x20Screenshot']===_0x33d6b7(0xe6),CGMZ[_0x33d6b7(0xd6)]['ScreenshotFolder']=CGMZ[_0x33d6b7(0xd6)][_0x33d6b7(0xc7)][_0x33d6b7(0xbb)],CGMZ['Screenshots'][_0x33d6b7(0xb9)]=CGMZ[_0x33d6b7(0xd6)]['parameters'][_0x33d6b7(0xd3)];const alias_CGMZ_Screenshots_registerPluginCommands=CGMZ_Temp['prototype']['registerPluginCommands'];function _0x1c6d(){const _0x21f353=['.png','canvas','getMilliseconds','parameters','toDataURL','value','24OxpXiW','2572808NYMMzq','getMonth','468116gKMWce','132916LdJMwg','saveScreenshot','PrintScreen','54mqZnYm','saveToLocalFile','Background\x20Color','prototype','width','Screenshots','registerPluginCommands','height','click','blt','toBlob','getFullYear','app','86910piCNNz','createScreenshotSprite','Screenshot_','126LtsTGN','createObjectURL','takeScreenshot','_inputCurrentState','body','true','registerCommand','AutomaticScreenshot','promptScreenshotDownload','refreshForKeysUp','_spriteset','getDate','1.0.1','getSeconds','remove','createElement','3636epXfnZ','getHours','replace','Versions','append','210WMHgAq','renderer','352219MRncTe','pluginCommandScreenshotsTakeScreenshot','4vYmVBJ','BGColor','href','Screenshot\x20Folder','image/png','363147ksaskN','ScreenshotFolder','hasOwnProperty','CGMZ_Screenshots','isNwjs','Take\x20Screenshot','snap'];_0x1c6d=function(){return _0x21f353;};return _0x1c6d();}CGMZ_Temp[_0x33d6b7(0xd4)][_0x33d6b7(0xd7)]=function(){const _0x1a7e76=_0x33d6b7;alias_CGMZ_Screenshots_registerPluginCommands['call'](this),PluginManager[_0x1a7e76(0xe7)](_0x1a7e76(0xc0),_0x1a7e76(0xc2),this['pluginCommandScreenshotsTakeScreenshot']);},CGMZ_Temp[_0x33d6b7(0xd4)][_0x33d6b7(0xb7)]=function(){$cgmzTemp['takeScreenshot']();},CGMZ_Temp[_0x33d6b7(0xd4)][_0x33d6b7(0xdf)]=function(){const _0x17c4eb=_0x33d6b7,_0x560005=Graphics[_0x17c4eb(0xd5)],_0x1cac6f=Graphics[_0x17c4eb(0xd8)],_0xfac78b=new Bitmap($gameVariables[_0x17c4eb(0xc9)](0x3),$gameVariables[_0x17c4eb(0xc9)](0x4)),_0x2f003a=Bitmap[_0x17c4eb(0xc3)](SceneManager['_scene'][_0x17c4eb(0xeb)]['_pictureContainer']);if(CGMZ['Screenshots'][_0x17c4eb(0xb9)])_0xfac78b['fillAll'](CGMZ['Screenshots'][_0x17c4eb(0xb9)]);return _0xfac78b[_0x17c4eb(0xda)](_0x2f003a,$gameVariables[_0x17c4eb(0xc9)](0x1),$gameVariables['value'](0x2),_0x560005,_0x1cac6f,0x0,0x0,_0x560005,_0x1cac6f),new Sprite(_0xfac78b);},CGMZ_Temp[_0x33d6b7(0xd4)][_0x33d6b7(0xe3)]=function(){const _0x2ef5e2=_0x33d6b7;if(Utils[_0x2ef5e2(0xc1)]()){const _0x121093=Graphics[_0x2ef5e2(0xdd)][_0x2ef5e2(0xb5)]['extract'][_0x2ef5e2(0xc5)](this['createScreenshotSprite']())[_0x2ef5e2(0xc8)](_0x2ef5e2(0xbc));this[_0x2ef5e2(0xcf)](_0x121093);}else Graphics['app'][_0x2ef5e2(0xb5)]['extract'][_0x2ef5e2(0xc5)](this[_0x2ef5e2(0xdf)]())[_0x2ef5e2(0xdb)](this[_0x2ef5e2(0xe9)](),_0x2ef5e2(0xbc));},CGMZ_Temp['prototype'][_0x33d6b7(0xcf)]=function(_0x998046){const _0x1eec11=_0x33d6b7;_0x998046=_0x998046[_0x1eec11(0xb1)](/^data:image\/png;base64,/,'');const _0x26a0e2=new Date(),_0x5b50d5=_0x26a0e2[_0x1eec11(0xdc)]()+'-'+(_0x26a0e2[_0x1eec11(0xcc)]()+0x1)+'-'+_0x26a0e2[_0x1eec11(0xec)]()+'_'+_0x26a0e2[_0x1eec11(0xb0)]()+_0x26a0e2['getMinutes']()+_0x26a0e2['getSeconds']()+_0x26a0e2[_0x1eec11(0xc6)](),_0x16e00d=CGMZ[_0x1eec11(0xd6)][_0x1eec11(0xbe)]+'/',_0x1c758f='PFCharacter_'+_0x5b50d5,_0x324d02=_0x1eec11(0xc4);this[_0x1eec11(0xd2)](_0x16e00d,_0x1c758f,_0x324d02,_0x998046);},CGMZ_Temp['prototype'][_0x33d6b7(0xe9)]=function(){return function(_0x284f5d){const _0x3d02b7=_0x5edc,_0x4d90cc=document[_0x3d02b7(0xae)]('a');document[_0x3d02b7(0xe5)][_0x3d02b7(0xb3)](_0x4d90cc);const _0x4f43b1=new Date(),_0xe17a9f=_0x4f43b1[_0x3d02b7(0xdc)]()+'-'+(_0x4f43b1[_0x3d02b7(0xcc)]()+0x1)+'-'+_0x4f43b1[_0x3d02b7(0xec)]()+'_'+_0x4f43b1['getHours']()+_0x4f43b1['getMinutes']()+_0x4f43b1[_0x3d02b7(0xee)]()+_0x4f43b1[_0x3d02b7(0xc6)]();_0x4d90cc['download']=_0x3d02b7(0xe0)+_0xe17a9f,_0x4d90cc[_0x3d02b7(0xba)]=URL[_0x3d02b7(0xe2)](_0x284f5d),_0x4d90cc[_0x3d02b7(0xd9)](),_0x4d90cc[_0x3d02b7(0xad)]();};};const alias_CGMZ_Screenshots_refreshForKeysUp=CGMZ_Temp[_0x33d6b7(0xd4)][_0x33d6b7(0xea)];CGMZ_Temp[_0x33d6b7(0xd4)]['refreshForKeysUp']=function(){const _0x26be01=_0x33d6b7;alias_CGMZ_Screenshots_refreshForKeysUp['call'](this),CGMZ[_0x26be01(0xd6)][_0x26be01(0xe8)]&&this[_0x26be01(0xe4)][_0x26be01(0xbf)](_0x26be01(0xd0))&&(this['takeScreenshot'](),delete this[_0x26be01(0xe4)][_0x26be01(0xd0)]);};
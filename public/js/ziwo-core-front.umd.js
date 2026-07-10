(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports) :
    typeof define === 'function' && define.amd ? define(['exports'], factory) :
    (global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory(global.ziwoCoreFront = {}));
}(this, (function (exports) { 'use strict';

    /*! *****************************************************************************
    Copyright (c) Microsoft Corporation.

    Permission to use, copy, modify, and/or distribute this software for any
    purpose with or without fee is hereby granted.

    THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
    REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
    AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
    INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
    LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
    OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
    PERFORMANCE OF THIS SOFTWARE.
    ***************************************************************************** */

    function __awaiter(thisArg, _arguments, P, generator) {
        function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
        return new (P || (P = Promise))(function (resolve, reject) {
            function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
            function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
            function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
            step((generator = generator.apply(thisArg, _arguments || [])).next());
        });
    }

    var Md5 = /** @class */ (function () {
        function Md5() {
        }
        Md5.AddUnsigned = function (lX, lY) {
            var lX4, lY4, lX8, lY8, lResult;
            lX8 = (lX & 0x80000000);
            lY8 = (lY & 0x80000000);
            lX4 = (lX & 0x40000000);
            lY4 = (lY & 0x40000000);
            lResult = (lX & 0x3FFFFFFF) + (lY & 0x3FFFFFFF);
            if (!!(lX4 & lY4)) {
                return (lResult ^ 0x80000000 ^ lX8 ^ lY8);
            }
            if (!!(lX4 | lY4)) {
                if (!!(lResult & 0x40000000)) {
                    return (lResult ^ 0xC0000000 ^ lX8 ^ lY8);
                }
                else {
                    return (lResult ^ 0x40000000 ^ lX8 ^ lY8);
                }
            }
            else {
                return (lResult ^ lX8 ^ lY8);
            }
        };
        Md5.FF = function (a, b, c, d, x, s, ac) {
            a = this.AddUnsigned(a, this.AddUnsigned(this.AddUnsigned(this.F(b, c, d), x), ac));
            return this.AddUnsigned(this.RotateLeft(a, s), b);
        };
        Md5.GG = function (a, b, c, d, x, s, ac) {
            a = this.AddUnsigned(a, this.AddUnsigned(this.AddUnsigned(this.G(b, c, d), x), ac));
            return this.AddUnsigned(this.RotateLeft(a, s), b);
        };
        Md5.HH = function (a, b, c, d, x, s, ac) {
            a = this.AddUnsigned(a, this.AddUnsigned(this.AddUnsigned(this.H(b, c, d), x), ac));
            return this.AddUnsigned(this.RotateLeft(a, s), b);
        };
        Md5.II = function (a, b, c, d, x, s, ac) {
            a = this.AddUnsigned(a, this.AddUnsigned(this.AddUnsigned(this.I(b, c, d), x), ac));
            return this.AddUnsigned(this.RotateLeft(a, s), b);
        };
        Md5.ConvertToWordArray = function (string) {
            var lWordCount, lMessageLength = string.length, lNumberOfWords_temp1 = lMessageLength + 8, lNumberOfWords_temp2 = (lNumberOfWords_temp1 - (lNumberOfWords_temp1 % 64)) / 64, lNumberOfWords = (lNumberOfWords_temp2 + 1) * 16, lWordArray = Array(lNumberOfWords - 1), lBytePosition = 0, lByteCount = 0;
            while (lByteCount < lMessageLength) {
                lWordCount = (lByteCount - (lByteCount % 4)) / 4;
                lBytePosition = (lByteCount % 4) * 8;
                lWordArray[lWordCount] = (lWordArray[lWordCount] | (string.charCodeAt(lByteCount) << lBytePosition));
                lByteCount++;
            }
            lWordCount = (lByteCount - (lByteCount % 4)) / 4;
            lBytePosition = (lByteCount % 4) * 8;
            lWordArray[lWordCount] = lWordArray[lWordCount] | (0x80 << lBytePosition);
            lWordArray[lNumberOfWords - 2] = lMessageLength << 3;
            lWordArray[lNumberOfWords - 1] = lMessageLength >>> 29;
            return lWordArray;
        };
        Md5.WordToHex = function (lValue) {
            var WordToHexValue = "", WordToHexValue_temp = "", lByte, lCount;
            for (lCount = 0; lCount <= 3; lCount++) {
                lByte = (lValue >>> (lCount * 8)) & 255;
                WordToHexValue_temp = "0" + lByte.toString(16);
                WordToHexValue = WordToHexValue + WordToHexValue_temp.substr(WordToHexValue_temp.length - 2, 2);
            }
            return WordToHexValue;
        };
        Md5.Utf8Encode = function (string) {
            var utftext = "", c;
            string = string.replace(/\r\n/g, "\n");
            for (var n = 0; n < string.length; n++) {
                c = string.charCodeAt(n);
                if (c < 128) {
                    utftext += String.fromCharCode(c);
                }
                else if ((c > 127) && (c < 2048)) {
                    utftext += String.fromCharCode((c >> 6) | 192);
                    utftext += String.fromCharCode((c & 63) | 128);
                }
                else {
                    utftext += String.fromCharCode((c >> 12) | 224);
                    utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                    utftext += String.fromCharCode((c & 63) | 128);
                }
            }
            return utftext;
        };
        Md5.init = function (string) {
            var temp;
            if (typeof string !== 'string')
                string = JSON.stringify(string);
            this._string = this.Utf8Encode(string);
            this.x = this.ConvertToWordArray(this._string);
            this.a = 0x67452301;
            this.b = 0xEFCDAB89;
            this.c = 0x98BADCFE;
            this.d = 0x10325476;
            for (this.k = 0; this.k < this.x.length; this.k += 16) {
                this.AA = this.a;
                this.BB = this.b;
                this.CC = this.c;
                this.DD = this.d;
                this.a = this.FF(this.a, this.b, this.c, this.d, this.x[this.k], this.S11, 0xD76AA478);
                this.d = this.FF(this.d, this.a, this.b, this.c, this.x[this.k + 1], this.S12, 0xE8C7B756);
                this.c = this.FF(this.c, this.d, this.a, this.b, this.x[this.k + 2], this.S13, 0x242070DB);
                this.b = this.FF(this.b, this.c, this.d, this.a, this.x[this.k + 3], this.S14, 0xC1BDCEEE);
                this.a = this.FF(this.a, this.b, this.c, this.d, this.x[this.k + 4], this.S11, 0xF57C0FAF);
                this.d = this.FF(this.d, this.a, this.b, this.c, this.x[this.k + 5], this.S12, 0x4787C62A);
                this.c = this.FF(this.c, this.d, this.a, this.b, this.x[this.k + 6], this.S13, 0xA8304613);
                this.b = this.FF(this.b, this.c, this.d, this.a, this.x[this.k + 7], this.S14, 0xFD469501);
                this.a = this.FF(this.a, this.b, this.c, this.d, this.x[this.k + 8], this.S11, 0x698098D8);
                this.d = this.FF(this.d, this.a, this.b, this.c, this.x[this.k + 9], this.S12, 0x8B44F7AF);
                this.c = this.FF(this.c, this.d, this.a, this.b, this.x[this.k + 10], this.S13, 0xFFFF5BB1);
                this.b = this.FF(this.b, this.c, this.d, this.a, this.x[this.k + 11], this.S14, 0x895CD7BE);
                this.a = this.FF(this.a, this.b, this.c, this.d, this.x[this.k + 12], this.S11, 0x6B901122);
                this.d = this.FF(this.d, this.a, this.b, this.c, this.x[this.k + 13], this.S12, 0xFD987193);
                this.c = this.FF(this.c, this.d, this.a, this.b, this.x[this.k + 14], this.S13, 0xA679438E);
                this.b = this.FF(this.b, this.c, this.d, this.a, this.x[this.k + 15], this.S14, 0x49B40821);
                this.a = this.GG(this.a, this.b, this.c, this.d, this.x[this.k + 1], this.S21, 0xF61E2562);
                this.d = this.GG(this.d, this.a, this.b, this.c, this.x[this.k + 6], this.S22, 0xC040B340);
                this.c = this.GG(this.c, this.d, this.a, this.b, this.x[this.k + 11], this.S23, 0x265E5A51);
                this.b = this.GG(this.b, this.c, this.d, this.a, this.x[this.k], this.S24, 0xE9B6C7AA);
                this.a = this.GG(this.a, this.b, this.c, this.d, this.x[this.k + 5], this.S21, 0xD62F105D);
                this.d = this.GG(this.d, this.a, this.b, this.c, this.x[this.k + 10], this.S22, 0x2441453);
                this.c = this.GG(this.c, this.d, this.a, this.b, this.x[this.k + 15], this.S23, 0xD8A1E681);
                this.b = this.GG(this.b, this.c, this.d, this.a, this.x[this.k + 4], this.S24, 0xE7D3FBC8);
                this.a = this.GG(this.a, this.b, this.c, this.d, this.x[this.k + 9], this.S21, 0x21E1CDE6);
                this.d = this.GG(this.d, this.a, this.b, this.c, this.x[this.k + 14], this.S22, 0xC33707D6);
                this.c = this.GG(this.c, this.d, this.a, this.b, this.x[this.k + 3], this.S23, 0xF4D50D87);
                this.b = this.GG(this.b, this.c, this.d, this.a, this.x[this.k + 8], this.S24, 0x455A14ED);
                this.a = this.GG(this.a, this.b, this.c, this.d, this.x[this.k + 13], this.S21, 0xA9E3E905);
                this.d = this.GG(this.d, this.a, this.b, this.c, this.x[this.k + 2], this.S22, 0xFCEFA3F8);
                this.c = this.GG(this.c, this.d, this.a, this.b, this.x[this.k + 7], this.S23, 0x676F02D9);
                this.b = this.GG(this.b, this.c, this.d, this.a, this.x[this.k + 12], this.S24, 0x8D2A4C8A);
                this.a = this.HH(this.a, this.b, this.c, this.d, this.x[this.k + 5], this.S31, 0xFFFA3942);
                this.d = this.HH(this.d, this.a, this.b, this.c, this.x[this.k + 8], this.S32, 0x8771F681);
                this.c = this.HH(this.c, this.d, this.a, this.b, this.x[this.k + 11], this.S33, 0x6D9D6122);
                this.b = this.HH(this.b, this.c, this.d, this.a, this.x[this.k + 14], this.S34, 0xFDE5380C);
                this.a = this.HH(this.a, this.b, this.c, this.d, this.x[this.k + 1], this.S31, 0xA4BEEA44);
                this.d = this.HH(this.d, this.a, this.b, this.c, this.x[this.k + 4], this.S32, 0x4BDECFA9);
                this.c = this.HH(this.c, this.d, this.a, this.b, this.x[this.k + 7], this.S33, 0xF6BB4B60);
                this.b = this.HH(this.b, this.c, this.d, this.a, this.x[this.k + 10], this.S34, 0xBEBFBC70);
                this.a = this.HH(this.a, this.b, this.c, this.d, this.x[this.k + 13], this.S31, 0x289B7EC6);
                this.d = this.HH(this.d, this.a, this.b, this.c, this.x[this.k], this.S32, 0xEAA127FA);
                this.c = this.HH(this.c, this.d, this.a, this.b, this.x[this.k + 3], this.S33, 0xD4EF3085);
                this.b = this.HH(this.b, this.c, this.d, this.a, this.x[this.k + 6], this.S34, 0x4881D05);
                this.a = this.HH(this.a, this.b, this.c, this.d, this.x[this.k + 9], this.S31, 0xD9D4D039);
                this.d = this.HH(this.d, this.a, this.b, this.c, this.x[this.k + 12], this.S32, 0xE6DB99E5);
                this.c = this.HH(this.c, this.d, this.a, this.b, this.x[this.k + 15], this.S33, 0x1FA27CF8);
                this.b = this.HH(this.b, this.c, this.d, this.a, this.x[this.k + 2], this.S34, 0xC4AC5665);
                this.a = this.II(this.a, this.b, this.c, this.d, this.x[this.k], this.S41, 0xF4292244);
                this.d = this.II(this.d, this.a, this.b, this.c, this.x[this.k + 7], this.S42, 0x432AFF97);
                this.c = this.II(this.c, this.d, this.a, this.b, this.x[this.k + 14], this.S43, 0xAB9423A7);
                this.b = this.II(this.b, this.c, this.d, this.a, this.x[this.k + 5], this.S44, 0xFC93A039);
                this.a = this.II(this.a, this.b, this.c, this.d, this.x[this.k + 12], this.S41, 0x655B59C3);
                this.d = this.II(this.d, this.a, this.b, this.c, this.x[this.k + 3], this.S42, 0x8F0CCC92);
                this.c = this.II(this.c, this.d, this.a, this.b, this.x[this.k + 10], this.S43, 0xFFEFF47D);
                this.b = this.II(this.b, this.c, this.d, this.a, this.x[this.k + 1], this.S44, 0x85845DD1);
                this.a = this.II(this.a, this.b, this.c, this.d, this.x[this.k + 8], this.S41, 0x6FA87E4F);
                this.d = this.II(this.d, this.a, this.b, this.c, this.x[this.k + 15], this.S42, 0xFE2CE6E0);
                this.c = this.II(this.c, this.d, this.a, this.b, this.x[this.k + 6], this.S43, 0xA3014314);
                this.b = this.II(this.b, this.c, this.d, this.a, this.x[this.k + 13], this.S44, 0x4E0811A1);
                this.a = this.II(this.a, this.b, this.c, this.d, this.x[this.k + 4], this.S41, 0xF7537E82);
                this.d = this.II(this.d, this.a, this.b, this.c, this.x[this.k + 11], this.S42, 0xBD3AF235);
                this.c = this.II(this.c, this.d, this.a, this.b, this.x[this.k + 2], this.S43, 0x2AD7D2BB);
                this.b = this.II(this.b, this.c, this.d, this.a, this.x[this.k + 9], this.S44, 0xEB86D391);
                this.a = this.AddUnsigned(this.a, this.AA);
                this.b = this.AddUnsigned(this.b, this.BB);
                this.c = this.AddUnsigned(this.c, this.CC);
                this.d = this.AddUnsigned(this.d, this.DD);
            }
            temp = this.WordToHex(this.a) + this.WordToHex(this.b) + this.WordToHex(this.c) + this.WordToHex(this.d);
            return temp.toLowerCase();
        };
        Md5.x = Array();
        Md5.S11 = 7;
        Md5.S12 = 12;
        Md5.S13 = 17;
        Md5.S14 = 22;
        Md5.S21 = 5;
        Md5.S22 = 9;
        Md5.S23 = 14;
        Md5.S24 = 20;
        Md5.S31 = 4;
        Md5.S32 = 11;
        Md5.S33 = 16;
        Md5.S34 = 23;
        Md5.S41 = 6;
        Md5.S42 = 10;
        Md5.S43 = 15;
        Md5.S44 = 21;
        Md5.RotateLeft = function (lValue, iShiftBits) { return (lValue << iShiftBits) | (lValue >>> (32 - iShiftBits)); };
        Md5.F = function (x, y, z) { return (x & y) | ((~x) & z); };
        Md5.G = function (x, y, z) { return (x & z) | (y & (~z)); };
        Md5.H = function (x, y, z) { return (x ^ y ^ z); };
        Md5.I = function (x, y, z) { return (y ^ (x | (~z))); };
        return Md5;
    }());

    const MESSAGE_PREFIX = '[LIB Ziwo-core-front] ';
    const MESSAGES = {
        EMAIL_PASSWORD_AUTHTOKEN_MISSING: `${MESSAGE_PREFIX}Email or password are missing and no authentication token were provided.`,
        INVALID_PHONE_NUMBER: (phoneNumber) => `${phoneNumber} is not a valid phone number`,
        AGENT_NOT_CONNECTED: (action) => `Agent is not connected. Cannot proceed '${action}'`,
        MEDIA_ERROR: `${MESSAGE_PREFIX}User media are not available`,
    };

    var UserStatus;
    (function (UserStatus) {
        UserStatus["Active"] = "active";
    })(UserStatus || (UserStatus = {}));
    var UserType;
    (function (UserType) {
        UserType["Admin"] = "admin";
    })(UserType || (UserType = {}));
    var UserProfileType;
    (function (UserProfileType) {
        UserProfileType["User"] = "users";
    })(UserProfileType || (UserProfileType = {}));
    class AuthenticationService {
        constructor() { }
        static authenticate(api, credentials) {
            if (credentials.authenticationToken) {
                api.setToken(credentials.authenticationToken);
                return new Promise((onRes, onErr) => {
                    Promise.all([
                        this.initAgent(api),
                        this.autoLogin(api),
                    ]).then(res => onRes(res[0])).catch(err => onErr(err));
                });
            }
            if (!credentials.email || !credentials.password) {
                throw new Error(MESSAGES.EMAIL_PASSWORD_AUTHTOKEN_MISSING);
            }
            return new Promise((onRes, onErr) => {
                this.loginZiwo(api, credentials.email, credentials.password).then(() => {
                    Promise.all([
                        this.initAgent(api),
                        this.autoLogin(api),
                    ]).then(res => {
                        onRes(res[0]);
                    }).catch(err => onErr(err));
                }).catch(err => onErr(err));
            });
        }
        static logout(api) {
            return api.put('/agents/logout', {});
        }
        static loginZiwo(api, email, password) {
            return new Promise((onRes, onErr) => {
                api.post(api.endpoints.authenticate, {
                    username: email,
                    password: password,
                }).then(r => {
                    api.setToken(r.content.access_token);
                    onRes(r.content);
                }).catch(e => {
                    onErr(e);
                });
            });
        }
        static autoLogin(api) {
            return api.put('/agents/autologin', {});
        }
        static initAgent(api) {
            return new Promise((onRes, onErr) => {
                Promise.all([
                    this.fetchAgentProfile(api),
                    this.fetchListQueues(api),
                    this.fetchListNumbers(api),
                    this.fetchWebRTCConfig(api),
                ]).then(res => {
                    onRes({
                        userInfo: res[0],
                        queues: res[1] || [],
                        numbers: res[2] || [],
                        webRtc: {
                            socket: `${res[3].webSocket.protocol}://${api.getHostname()}:${res[3].webSocket.port}`,
                        },
                        position: {
                            name: `agent-${res[0].ccLogin}`,
                            password: Md5.init(`${res[0].ccLogin}${res[0].ccPassword}`).toString(),
                            hostname: api.getHostname(),
                        }
                    });
                })
                    .catch(err => onErr(err));
            });
        }
        static fetchAgentProfile(api) {
            return new Promise((onRes, onErr) => {
                api.get(api.endpoints.profile).then(res => {
                    onRes(res.content);
                }).catch(err => onErr(err));
            });
        }
        static fetchListQueues(api) {
            return new Promise((onRes, onErr) => {
                api.get('/agents/channels/calls/listQueues').then(res => {
                    onRes(res.content);
                }).catch(err => onErr(err));
            });
        }
        static fetchListNumbers(api) {
            return new Promise((onRes, onErr) => {
                api.get('/agents/channels/calls/listNumbers').then(res => {
                    onRes(res.content);
                }).catch(err => onErr(err));
            });
        }
        static fetchWebRTCConfig(api) {
            return new Promise((onRes, onErr) => {
                api.get('/fs/webrtc/config').then(res => {
                    onRes(res.content);
                }).catch(err => onErr(err));
            });
        }
    }

    /**
     * ApiService provide functions for GET, POST, PUT and DELETE query
     *
     * Usage:
     *
     *  const apiService = new ApiService('kalvad-poc'); // contact center name you want to connect to
     *  apiService.get<User>(apiService.endpoints.authenticate) ; // ApiService already defined the endpoints available on Ziwo API
     *    .then( (e) => console.log('User > ', e.data)); // Request object is available under `data`;
     */
    class ApiService {
        constructor(contactCenterName) {
            this.API_PROTOCOL = 'https://';
            this.API_PREFIX = '-api.aswat.co';
            this.contactCenterName = contactCenterName;
            this.baseUrl = `${this.API_PROTOCOL}${contactCenterName}${this.API_PREFIX}`;
            this.endpoints = {
                authenticate: `/auth/login`,
                profile: '/profile',
                autologin: '/agents/autoLogin',
                click2Call: '/integrations/cti/agents/call',
            };
        }
        /**
         * Return the hostname of current user
         */
        getHostname() {
            return `${this.contactCenterName}${this.API_PREFIX}`;
        }
        /**
         * Set Authorization token for further requests
         */
        setToken(token) {
            this.token = token;
        }
        /**
         * Execute a GET query
         * @endpoint url endpoint. Base url should not be included
         */
        get(endpoint) {
            return this.query(endpoint + `?bc=${Date.now()}`, 'GET');
        }
        /**
         * Execute a POST query
         * @endpoint url endpoint. Base url should not be included
         */
        post(endpoint, payload) {
            return this.query(endpoint + `?bc=${Date.now()}`, 'POST', payload);
        }
        /**
         * Execute a PUT query
         * @endpoint url endpoint. Base url should not be included
         */
        put(endpoint, payload) {
            return this.query(endpoint + `?bc=${Date.now()}`, 'PUT', payload);
        }
        /**
         * Execute a DELETE query
         * @endpoint url endpoint. Base url should not be included
         */
        delete(endpoint) {
            return this.query(endpoint + `?bc=${Date.now()}`, 'DELETE');
        }
        query(endpoint, method, payload) {
            return new Promise((onRes, onErr) => {
                const fetchOptions = {
                    method: method,
                };
                if (payload) {
                    fetchOptions.body = JSON.stringify(payload);
                }
                window.fetch(`${this.baseUrl}${endpoint}`, {
                    method: method,
                    body: payload ? JSON.stringify(payload) : undefined,
                    headers: {
                        'Content-Type': 'application/json',
                        'access_token': `${this.token}`,
                    }
                }).then(res => {
                    if (!res.ok) {
                        onErr(`Fetch error: ${res.statusText}`);
                        return;
                    }
                    onRes(res.json());
                }).catch(err => onErr(err));
            });
        }
    }

    var ErrorCode;
    (function (ErrorCode) {
        ErrorCode[ErrorCode["InvalidPhoneNumber"] = 2] = "InvalidPhoneNumber";
        ErrorCode[ErrorCode["UserMediaError"] = 3] = "UserMediaError";
        ErrorCode[ErrorCode["AgentNotConnected"] = 1] = "AgentNotConnected";
        ErrorCode[ErrorCode["ProtocolError"] = 4] = "ProtocolError";
    })(ErrorCode || (ErrorCode = {}));
    var ZiwoErrorCode;
    (function (ZiwoErrorCode) {
        ZiwoErrorCode[ZiwoErrorCode["ProtocolError"] = 1001] = "ProtocolError";
        ZiwoErrorCode[ZiwoErrorCode["MediaError"] = 1002] = "MediaError";
        ZiwoErrorCode[ZiwoErrorCode["MissingCall"] = 1003] = "MissingCall";
        ZiwoErrorCode[ZiwoErrorCode["CannotCreateCall"] = 1004] = "CannotCreateCall";
        ZiwoErrorCode[ZiwoErrorCode["DevicesError"] = 1005] = "DevicesError";
        ZiwoErrorCode[ZiwoErrorCode["DevicesErrorNoInput"] = 10051] = "DevicesErrorNoInput";
        ZiwoErrorCode[ZiwoErrorCode["DevicesErrorNoOutout"] = 10052] = "DevicesErrorNoOutout";
    })(ZiwoErrorCode || (ZiwoErrorCode = {}));
    var ZiwoEventType;
    (function (ZiwoEventType) {
        ZiwoEventType["Error"] = "error";
        ZiwoEventType["Connected"] = "connected";
        ZiwoEventType["Disconnected"] = "disconnected";
        ZiwoEventType["Requesting"] = "requesting";
        ZiwoEventType["Trying"] = "trying";
        ZiwoEventType["Early"] = "early";
        ZiwoEventType["Ringing"] = "ringing";
        ZiwoEventType["Answering"] = "answering";
        ZiwoEventType["Active"] = "active";
        ZiwoEventType["Held"] = "held";
        ZiwoEventType["Unheld"] = "unheld";
        ZiwoEventType["MediaConnected"] = "media-connected";
        ZiwoEventType["Hangup"] = "hangup";
        ZiwoEventType["Mute"] = "mute";
        ZiwoEventType["Unmute"] = "unmute";
        ZiwoEventType["Purge"] = "purge";
        ZiwoEventType["Destroy"] = "destroy";
        ZiwoEventType["Recovering"] = "recovering";
        ZiwoEventType["OutputChanged"] = "output-changed";
        ZiwoEventType["InputChanged"] = "input-changed";
        ZiwoEventType["VertoSend"] = "verto-send";
        ZiwoEventType["VertoSendToAgentOnCall"] = "verto-sendToAgentOnCall";
    })(ZiwoEventType || (ZiwoEventType = {}));
    /**
     * All phone call (outbound & inbound) will throw events during their lifetime.
     * Here is the expected cycle of a phone call:
     *  1. requesting (event thrown only for outbound call)
     *  2. trying (event thrown only for outbound call)
     *  3. early (event thrown only for outbound call)
     *  4. ringing
     *  5. answering
     *  6. active (peers are able to talk)
     *  6.x call can changes states multiple time (hold, unhold, ...)
     *  7. hangup (call stops and peers are not able to talk anymore)
     *  8. purge (event is going to be destroy)
     *  9. event is destroyed
     *
     * All call not going through step 7, 8 and 9 will be automatically recovered in case user refresh the page
     */
    class ZiwoEvent {
        constructor(type, data) {
            this.type = type;
            this.data = data;
        }
        static subscribe(func) {
            this.listeners.push(func);
        }
        static emit(type, data) {
            this.listeners.forEach(x => x(type, data));
            this.dispatchEvents(type, data);
        }
        static error(code, data) {
            this.dispatchEvents(ZiwoEventType.Error, {
                code: code,
                inner: data,
            });
        }
        static dispatchEvents(type, data) {
            this.prefixes.forEach(p => window.dispatchEvent(new CustomEvent(`${p}${type}`, { detail: data })));
        }
        emit() {
            ZiwoEvent.emit(this.type, this.data);
        }
    }
    ZiwoEvent.listeners = [];
    ZiwoEvent.prefixes = ['_jorel-dialog-state-', 'ziwo-'];

    var VertoMethod;
    (function (VertoMethod) {
        VertoMethod["Login"] = "login";
        VertoMethod["ClientReady"] = "verto.clientReady";
        VertoMethod["Send"] = "verto.send";
        VertoMethod["Attach"] = "verto.attach";
        VertoMethod["Media"] = "verto.media";
        VertoMethod["Invite"] = "verto.invite";
        VertoMethod["Answer"] = "verto.answer";
        VertoMethod["Info"] = "verto.info";
        VertoMethod["Modify"] = "verto.modify";
        VertoMethod["Display"] = "verto.display";
        VertoMethod["Bye"] = "verto.bye";
        VertoMethod["Pickup"] = "verto.pickup";
        VertoMethod["Dial"] = "verto.dial";
        VertoMethod["SendToAgentOnCall"] = "verto.sendToAgentOnCall";
    })(VertoMethod || (VertoMethod = {}));
    var VertoByeReason;
    (function (VertoByeReason) {
        VertoByeReason[VertoByeReason["NORMAL_CLEARING"] = 16] = "NORMAL_CLEARING";
        VertoByeReason[VertoByeReason["CALL_REJECTED"] = 21] = "CALL_REJECTED";
        VertoByeReason[VertoByeReason["ORIGINATOR_CANCEL"] = 487] = "ORIGINATOR_CANCEL";
    })(VertoByeReason || (VertoByeReason = {}));
    var VertoState;
    (function (VertoState) {
        VertoState["Hold"] = "hold";
        VertoState["Unhold"] = "unhold";
        VertoState["Purge"] = "purge";
    })(VertoState || (VertoState = {}));
    var VertoNotificationMessage;
    (function (VertoNotificationMessage) {
        VertoNotificationMessage["CallCreated"] = "CALL CREATED";
        VertoNotificationMessage["CallEnded"] = "CALL ENDED";
    })(VertoNotificationMessage || (VertoNotificationMessage = {}));
    class VertoParams {
        constructor() {
            this.id = 0;
        }
        wrap(method, params = {}, id = -1) {
            this.id += 1;
            return {
                jsonrpc: '2.0',
                method: method,
                id: id > 0 ? id : this.id,
                params: params,
            };
        }
        login(sessid, login, passwd) {
            return this.wrap(VertoMethod.Login, {
                sessid,
                login,
                passwd
            });
        }
        startCall(sessionId, callId, login, phoneNumber, sdp) {
            return this.wrap(VertoMethod.Invite, {
                sdp: sdp,
                sessid: sessionId,
                dialogParams: this.dialogParams(callId, login, phoneNumber),
            });
        }
        hangupCall(sessionId, callId, login, phoneNumber, reason = VertoByeReason.NORMAL_CLEARING) {
            return this.wrap(VertoMethod.Bye, {
                cause: VertoByeReason[reason],
                causeCode: reason,
                dialogParams: this.dialogParams(callId, login, phoneNumber),
                sessid: sessionId,
            });
        }
        attach(sessionId, callId, login, phoneNumber, sdp) {
            return this.wrap(VertoMethod.Attach, {
                sdp: sdp,
                sessid: sessionId,
                dialogParams: this.dialogParams(callId, login, phoneNumber),
            });
        }
        answerCall(sessionId, callId, login, phoneNumber, sdp) {
            return this.wrap(VertoMethod.Answer, {
                sdp: sdp,
                sessid: sessionId,
                dialogParams: this.dialogParams(callId, login, phoneNumber, 'Inbound Call')
            });
        }
        setState(sessionId, callId, login, phoneNumber, state) {
            return this.wrap(VertoMethod.Modify, {
                action: state,
                dialogParams: this.dialogParams(callId, login, phoneNumber),
                sessid: sessionId,
            });
        }
        transfer(sessionId, callId, login, phoneNumber, transferTo) {
            return this.wrap(VertoMethod.Modify, {
                action: 'transfer',
                destination: transferTo,
                dialogParams: this.dialogParams(callId, login, phoneNumber),
                sessid: sessionId,
            });
        }
        dtmf(sessionId, callId, login, phoneNumber, char) {
            return this.wrap(VertoMethod.Info, {
                sessid: sessionId,
                dtmf: char,
                dialogParams: this.dialogParams(callId, login, phoneNumber)
            });
        }
        getUuid() {
            return VertoParams.getUuid();
        }
        static getUuid() {
            /* tslint:disable */
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                const r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
                /* tslint:enable */
            });
        }
        dialogParams(callId, login, phoneNumber, callName = 'Outbound Call') {
            return {
                callID: callId,
                caller_id_name: '',
                caller_id_number: '',
                dedEnc: false,
                destination_number: phoneNumber,
                incomingBandwidth: 'default',
                localTag: null,
                login: login,
                outgoingBandwidth: 'default',
                remote_caller_id_name: 'Outbound Call',
                remote_caller_id_number: phoneNumber,
                screenShare: false,
                tag: this.getUuid(),
                useCamera: false,
                useMic: true,
                useSpeak: true,
                useStereo: true,
                useVideo: undefined,
                videoParams: {},
                audioParams: {
                    googAutoGainControl: false,
                    googNoiseSuppression: false,
                    googHighpassFilter: false
                },
            };
        }
    }

    var CallStatus;
    (function (CallStatus) {
        CallStatus["Stopped"] = "stopped";
        CallStatus["Running"] = "running";
        CallStatus["OnHold"] = "onHold";
    })(CallStatus || (CallStatus = {}));
    /**
     * Call hold a physical instance of a call.
     * They provide useful information but also methods to change the state of the call.
     *
     * @callId : unique identifier used for Jorel protocol
     * @primaryCallId : link to first call of the chain if existing
     * @rtcPeerConnection : the WebRTC interface
     * @channel : holds the media stream (input/output)
     * @verto : holds a reference to Verto singleton
     * @phoneNumber : peer phone number
     * @direction : call's direction
     * @states : array containing all the call's status update with a Datetime.
     * @initialPayload : complete payload received/sent to start the call
     */
    class Call {
        constructor(callId, verto, phoneNumber, login, rtcPeerConnection, channel, direction, initialPayload) {
            this.states = [];
            this.verto = verto;
            this.callId = callId;
            this.channel = channel;
            this.verto = verto;
            this.rtcPeerConnection = rtcPeerConnection;
            this.phoneNumber = phoneNumber;
            this.direction = direction;
            this.initialPayload = initialPayload;
            if (this.initialPayload && this.initialPayload.verto_h_primaryCallID) {
                this.primaryCallId = this.initialPayload.verto_h_primaryCallID;
            }
        }
        /**
         * Use when current state is `ringing` to switch the call to `active`
         */
        answer() {
            var _a;
            return this.verto.answerCall(this.callId, this.phoneNumber, (_a = this.rtcPeerConnection.localDescription) === null || _a === void 0 ? void 0 : _a.sdp);
        }
        /**
         * Use when current state is 'ringing' or 'active' to stop the call
         */
        hangup() {
            this.pushState(ZiwoEventType.Hangup, { origin: 'agent' });
            let reason = VertoByeReason.NORMAL_CLEARING;
            if (this.direction === 'inbound' && this.states.findIndex(x => x.state === ZiwoEventType.Active) === -1) {
                reason = VertoByeReason.CALL_REJECTED;
            }
            if (this.direction === 'outbound' && this.states.findIndex(x => x.state === ZiwoEventType.Answering) === -1) {
                reason = VertoByeReason.ORIGINATOR_CANCEL;
            }
            this.verto.hangupCall(this.callId, this.phoneNumber, reason);
        }
        /**
         * Recover the call currently in recovering state
         */
        recover() {
            var _a;
            this.verto.attach(this.callId, this.phoneNumber, (_a = this.rtcPeerConnection.localDescription) === null || _a === void 0 ? void 0 : _a.sdp);
        }
        /**
         * Use to send a digit
         */
        dtmf(char) {
            this.verto.dtmf(this.callId, this.phoneNumber, char);
        }
        /**
         * Set the call on hold
         */
        hold() {
            this.verto.holdCall(this.callId, this.phoneNumber);
        }
        /**
         * Unhold the call
         */
        unhold() {
            // we hold other calls
            this.verto.calls.forEach(c => {
                if (c.callId !== this.callId) {
                    c.hold();
                }
            });
            this.verto.unholdCall(this.callId, this.phoneNumber);
        }
        /**
         * Mute user's microphone
         */
        mute() {
            this.toggleSelfStream(false);
            // Because mute is not sent/received over the socket, we throw the event manually
            this.pushState(ZiwoEventType.Mute);
        }
        /**
         * Unmute user's microphone
         */
        unmute() {
            this.toggleSelfStream(true);
            this.pushState(ZiwoEventType.Unmute);
            // Because unmute is not sent/received over the socket, we throw the event manually
        }
        /**
         * Start an attended transfer.
         * Attended transfer set the current call on hold and call @destination
         * Use `proceedAttendedTransfer` to confirm the transfer
         */
        attendedTransfer(destination) {
            return __awaiter(this, void 0, void 0, function* () {
                this.hold();
                const call = yield this.verto.startCall(destination);
                if (!call) {
                    return undefined;
                }
                this.verto.calls.push(call);
                return call;
            });
        }
        /**
         * Confirm an attended transfer.
         * Stop the current call and create a new call between the initial correspondant and the @destination
         */
        proceedAttendedTransfer(transferCall) {
            if (!transferCall) {
                return;
            }
            const destination = transferCall.phoneNumber;
            transferCall.hangup();
            this.blindTransfer(destination);
        }
        /**
         * Stop the current call and directly forward the correspondant to @destination
         */
        blindTransfer(destination) {
            this.verto.blindTransfer(destination, this.callId, this.phoneNumber);
        }
        /**
         * Push state add a new state in the stack and throw an event
         */
        pushState(type, payload) {
            const d = new Date();
            this.states.push({
                state: type,
                date: d,
                dateUNIX: d.getTime() / 1000
            });
            ZiwoEvent.emit(type, Object.assign(Object.assign({}, payload), { type, currentCall: this, primaryCallID: this.primaryCallId, callID: this.callId, direction: this.direction, stateFlow: this.states, customerNumber: this.phoneNumber }));
        }
        toggleSelfStream(enabled) {
            this.channel.stream.getAudioTracks().forEach((tr) => {
                tr.enabled = enabled;
            });
        }
    }

    class HTMLMediaElementFactory {
        static push(io, parent, callId, type) {
            return __awaiter(this, void 0, void 0, function* () {
                const t = document.createElement('video');
                t.id = `media-${type}-${callId}`;
                t.setAttribute('playsinline', '');
                t.setAttribute('autoplay', '');
                t.dataset.callId = callId;
                t.dataset.type = type;
                t.volume = io.volume / 100;
                parent.appendChild(t);
                window.setTimeout(() => {
                    t.setSinkId(io.output.deviceId)
                        .then(() => {
                        var _a;
                        console.log(`Success, audio output device attached: ${(_a = io.output) === null || _a === void 0 ? void 0 : _a.deviceId}`);
                    })
                        .catch((error) => {
                        let errorMessage = error;
                        if (error.name === 'SecurityError') {
                            errorMessage = `You need to use HTTPS for selecting audio output device: ${error}`;
                        }
                        console.error(errorMessage);
                    });
                }, 200);
                return t;
            });
        }
        static delete(parent, callId) {
            const toRemove = [];
            for (let i = 0; i < parent.children.length; i++) {
                const item = parent.children[i];
                if (item && item.dataset && item.dataset.callId === callId) {
                    toRemove.push(item);
                }
            }
            toRemove.forEach(e => e.remove());
        }
    }

    class RTCPeerConnectionFactory {
        /**
         * We initiate the call
         */
        static outbound(verto, callId, login, phoneNumber) {
            return __awaiter(this, void 0, void 0, function* () {
                const rtcPeerConnection = new RTCPeerConnection({
                    iceServers: this.STUN_ICE_SERVER.map(x => {
                        return { urls: x };
                    })
                });
                const channel = yield verto.io.getChannel();
                console.log('channel', channel);
                if (!channel) {
                    throw new Error('could not retrieve microphone');
                }
                rtcPeerConnection.ontrack = (tr) => {
                    const track = tr.track;
                    if (track.kind !== 'audio') {
                        return;
                    }
                    const stream = new MediaStream();
                    stream.addTrack(track);
                    if (channel) {
                        channel.remoteStream = stream;
                    }
                    HTMLMediaElementFactory.push(verto.io, verto.tag, callId, 'peer').then(e => {
                        e.srcObject = stream;
                        return rtcPeerConnection;
                    });
                };
                // Attach our media stream to the call's PeerConnection
                channel.stream.getTracks().forEach((track) => {
                    rtcPeerConnection.addTrack(track);
                });
                // We wait for candidate to be null to make sure all candidates have been processed
                // ! for unknown reason, gathering ice candidates on chrome while using a VPN is taking too long (up to 1 min)
                //   so we use a timeout to cancel process with if collecting takes too long
                let collectingDone = false;
                let collectTimeout;
                rtcPeerConnection.onicecandidate = (candidate) => {
                    var _a;
                    if (collectTimeout) {
                        window.clearTimeout(collectTimeout);
                    }
                    if (collectingDone) {
                        return;
                    }
                    if (!candidate.candidate) {
                        verto.send(verto.params.startCall(verto.sessid, callId, login, phoneNumber, (_a = rtcPeerConnection.localDescription) === null || _a === void 0 ? void 0 : _a.sdp));
                    }
                    else {
                        collectTimeout = window.setTimeout(() => {
                            var _a;
                            collectingDone = true;
                            verto.send(verto.params.startCall(verto.sessid, callId, login, phoneNumber, (_a = rtcPeerConnection.localDescription) === null || _a === void 0 ? void 0 : _a.sdp));
                        }, 1000);
                    }
                };
                rtcPeerConnection.createOffer().then((offer) => {
                    rtcPeerConnection.setLocalDescription(offer).then(() => { });
                });
                return [rtcPeerConnection, channel];
            });
        }
        /**
         * We receive the call
         */
        static inbound(verto, inboudParams) {
            return __awaiter(this, void 0, void 0, function* () {
                const channel = yield verto.io.getChannel();
                const rtcPeerConnection = new RTCPeerConnection({
                    iceServers: this.STUN_ICE_SERVER.map(x => {
                        return { urls: x };
                    })
                });
                rtcPeerConnection.ontrack = (tr) => {
                    const track = tr.track;
                    if (track.kind !== 'audio') {
                        return;
                    }
                    const stream = new MediaStream();
                    stream.addTrack(track);
                    if (!channel) {
                        return;
                    }
                    channel.remoteStream = stream;
                    HTMLMediaElementFactory.push(verto.io, verto.tag, inboudParams.callID, 'peer').then(r => {
                        r.srcObject = stream;
                        return rtcPeerConnection;
                    });
                };
                if (!channel) {
                    throw new Error('could not retrieve microphone');
                }
                // Attach our media stream to the call's PeerConnection
                channel.stream.getTracks().forEach((track) => {
                    rtcPeerConnection.addTrack(track);
                });
                rtcPeerConnection.setRemoteDescription(new RTCSessionDescription({ type: 'offer', sdp: inboudParams.sdp }))
                    .then(() => {
                    rtcPeerConnection.createAnswer().then(d => {
                        rtcPeerConnection.setLocalDescription(d);
                    });
                });
                return [rtcPeerConnection, channel];
            });
        }
        static recovering(verto, params, _direction) {
            // recovering is processed as an incoming call regardless of the initial direction
            return this.inbound(verto, params);
        }
    }
    RTCPeerConnectionFactory.STUN_ICE_SERVER = ['stun:stun.l.google.com:19302'];

    /**
     * Verto Orchestrator can be seen as the core component of our Verto implemented
     * Its role is to read all incoming message and act appropriately:
     *  - broadcast important messages as ZiwoEvent (incoming call, call set on hold, call answered, ...)
     *  - run appropriate commands if required by verto protocol (bind stream on verto.mediaRequest, clear call if verto.callDestroyed, ...)
     */
    class VertoOrchestrator {
        constructor(verto, debug) {
            this.debug = debug;
            this.verto = verto;
        }
        /**
         * We can identify 2 types of inputs:
         *  - message (or request): contains a `method` and usually requires further actions
         *  - notication: does not contain a `method` and does not require further actions. Provide call's update (hold, unhold, ...)
         */
        onInput(message, call) {
            return message.method ? this.handleMessage(message, call) : this.handleNotification(message, call);
        }
        handleMessage(message, call) {
            if (this.debug) {
                console.log('Incoming message ', message);
            }
            switch (message.method) {
                case VertoMethod.ClientReady:
                    return this.onClientReady(message);
                case VertoMethod.Send:
                    return this.onSend(message);
                case VertoMethod.Attach:
                    return this.onAttach(message);
                case VertoMethod.Media:
                    return !this.ensureCallIsExisting(call) ? undefined
                        : this.onMedia(message, call);
                case VertoMethod.Invite:
                    this.pushState(call, ZiwoEventType.Trying);
                    return this.onInvite(message);
                case VertoMethod.Answer:
                    this.pushState(call, ZiwoEventType.Answering);
                    return !this.ensureCallIsExisting(call) ? undefined
                        : this.onAnswer(message, call);
                case VertoMethod.Display:
                    if (this.ensureCallIsExisting(call)) {
                        const c = call;
                        if (c.states && c.states.length > 0 && c.states[c.states.length - 1].state !== ZiwoEventType.Active) {
                            c.pushState(ZiwoEventType.Active);
                        }
                    }
                    break;
                case VertoMethod.Pickup:
                    if (this.ensureCallIsExisting(call)) {
                        this.pickup(message, call);
                    }
                    break;
                case VertoMethod.Bye:
                    if (this.ensureCallIsExisting(call)) {
                        call.pushState(ZiwoEventType.Hangup, { origin: 'interlocutor' });
                        this.verto.purgeAndDestroyCall(call.callId);
                    }
                    break;
                case VertoMethod.Dial:
                    this.verto.startCall(message.params.number, message.params.uuid);
                    break;
                case VertoMethod.SendToAgentOnCall:
                    return this.onSendToAgentOnCall(message);
            }
            return undefined;
        }
        handleNotification(message, call) {
            if (this.debug) {
                console.log('Incoming notification ', message);
            }
            if (message.result && message.result.message) {
                switch (message.result.message) {
                    case VertoNotificationMessage.CallCreated:
                        if (this.ensureCallIsExisting(call)) {
                            call.pushState(ZiwoEventType.Early);
                        }
                        break;
                    case VertoNotificationMessage.CallEnded:
                        if (this.ensureCallIsExisting(call)) {
                            call.pushState(ZiwoEventType.Hangup, { origin: 'call ended' });
                        }
                }
            }
            if (message.result && message.result.action) {
                switch (message.result.action) {
                    case VertoState.Hold:
                        return !this.ensureCallIsExisting(call) ? undefined
                            : this.onHold(call);
                    case VertoState.Unhold:
                        return !this.ensureCallIsExisting(call) ? undefined
                            : this.onUnhold(call);
                }
            }
            return undefined;
        }
        onClientReady(message) {
            ZiwoEvent.emit(ZiwoEventType.Connected, {
                agent: this.verto.connectedAgent,
                contactCenterName: this.verto.contactCenterName,
            });
        }
        onSend(message) {
            ZiwoEvent.emit(ZiwoEventType.VertoSend, {
                callParams: message.params.data
            });
        }
        onSendToAgentOnCall(message) {
            ZiwoEvent.emit(ZiwoEventType.VertoSendToAgentOnCall, {
                callParams: message.params.data
            });
        }
        /***
         *** MESSAGE SECTION
         ***/
        /**
         * OnMedia requires to bind incoming Stream to our call's RtcPeerConnection
         * It should be transparent to users. No need to broadcast the event
         */
        onMedia(message, call) {
            call.rtcPeerConnection.setRemoteDescription(new RTCSessionDescription({ type: 'answer', sdp: message.params.sdp }))
                .then(() => {
                if (this.debug) {
                    console.log('Remote media connected');
                }
                call.pushState(ZiwoEventType.MediaConnected);
            }).catch(() => {
                if (this.debug) {
                    console.warn('fail to attach remote media');
                }
            });
        }
        onInvite(message) {
            RTCPeerConnectionFactory
                .inbound(this.verto, message.params)
                .then(res => {
                const pc = res[0];
                const channel = res[1];
                const call = new Call(message.params.callID, this.verto, message.params.verto_h_originalCallerIdNumber ? message.params.verto_h_originalCallerIdNumber : message.params.caller_id_number, this.verto.getLogin(), pc, channel, 'inbound', message.params);
                this.verto.calls.push(call);
                call.pushState(ZiwoEventType.Ringing);
            });
        }
        /**
         * Automatically create a phone call instance and reply to it in the background
         * used for Zoho CTI
         */
        pickup(message, call) {
            call.answer();
        }
        /** Recovering call */
        onAttach(message) {
            RTCPeerConnectionFactory.recovering(this.verto, message.params, message.params.display_direction)
                .then(res => {
                const pc = res[0];
                const channel = res[1];
                const call = new Call(message.params.callID, this.verto, message.params.display_direction === 'inbound' ? message.params.callee_id_number : message.params.caller_id_number, this.verto.getLogin(), pc, channel, message.params.display_direction, message.params);
                this.verto.calls.push(call);
                // so SDP has time to build
                window.setTimeout(() => call.pushState(ZiwoEventType.Recovering), 500);
            });
        }
        /**
         * Call has been answered by remote. Broadcast the event
         */
        onAnswer(message, call) {
            call.pushState(ZiwoEventType.Answering);
        }
        /***
         *** NOTIFICATION SECTION
         ***/
        onHold(call) {
            call.pushState(ZiwoEventType.Held);
        }
        onUnhold(call) {
            call.pushState(ZiwoEventType.Unheld);
        }
        /***
         *** OTHERS
         ***/
        /**
         * ensureCallIsExisting makes sure the call is not undefined.
         * If it is undefined, throw a meaningful error message
         */
        ensureCallIsExisting(call) {
            if (!call) {
                ZiwoEvent.error(ZiwoErrorCode.MissingCall, 'Received event from unknown callID');
                return false;
            }
            return true;
        }
        pushState(call, state) {
            if (call) {
                call.pushState(state);
            }
        }
    }

    class VertoClear {
        constructor(verto, debug) {
            this.verto = verto;
            this.debug = debug;
            this.eventType = this.debug ? 'beforeunload' : 'unload';
        }
        /**
         * When user closes the tab, we need to purge the call
         *  - for on going call(s): purge
         *  - for ended call(s): purge + destroy
         */
        prepareUnloadEvent() {
            window.addEventListener(this.eventType, (e) => {
                this.purge(this.verto.calls);
            }, false);
            if (this.debug) ;
        }
        destroyCall(call) {
            if (call.channel.stream) {
                // tslint:disable-next-line: triple-equals
                if (typeof call.channel.stream.stop == 'function') {
                    call.channel.stream.stop();
                }
                else {
                    if (call.channel.stream.active) {
                        const tracks = call.channel.stream.getTracks();
                        tracks.forEach((tr) => tr.stop());
                    }
                }
            }
            // tslint:disable-next-line: triple-equals
            if (call.channel.remoteStream && call.channel.remoteStream == 'function') {
                call.channel.remoteStream.stop();
            }
            HTMLMediaElementFactory.delete(this.verto.tag, call.callId);
        }
        purge(calls) {
            if (this.debug) {
                console.log('PURGE > ', calls);
            }
            calls.forEach(c => this.verto.purgeCall(c.callId));
        }
    }

    class VertoSession {
        /**
         * get will fetch local storage to retrieve existing SessionId
         * If no SessionId are found in storage, we generate a new one
         */
        static get() {
            const v = window.sessionStorage.getItem(this.storageKey);
            if (v) {
                return v;
            }
            const newId = VertoParams.getUuid();
            window.sessionStorage.setItem(this.storageKey, newId);
            return newId;
        }
    }
    VertoSession.storageKey = 'ziwo_socket_session_id';

    /**
     * JsonRpcClient implements Verto protocol using JSON RPC
     *
     * Usage:
     *  - const client = new JsonRpcClient(@debug); // Instantiate a new Json Rpc Client
     *  - client.openSocket(@socketUrl) // REQUIRED: Promise opening the web socket
     *      .then(() => {
     *        this.login() // REQUIRED: log the agent into the web socket
     *        // You can now proceed with any requests
     *      });
     *
     */
    class Verto {
        constructor(calls, debug, tag, io) {
            /**
             * Callback functions - register using `addListener`
             */
            this.listeners = [];
            this.debug = debug;
            // this.tags = tags;
            this.tag = tag;
            this.orchestrator = new VertoOrchestrator(this, this.debug);
            this.cleaner = new VertoClear(this, this.debug);
            this.params = new VertoParams();
            this.calls = calls;
            this.io = io;
        }
        /**
         * addListener allows to listen for incoming Socket Event
         */
        addListener(call) {
            this.listeners.push(call);
        }
        connectAgent(agent, contactCenterName) {
            return new Promise((onRes, onErr) => {
                // First we make ensure access to microphone &| camera
                // And wait for the socket to open
                this.connectedAgent = agent;
                this.contactCenterName = contactCenterName;
                Promise.all([
                    this.openSocket(agent.webRtc.socket),
                ]).then(res => {
                    this.login(agent.position);
                }).catch(err => {
                    onErr(err);
                });
            });
        }
        /**
         * send a start call request
         */
        startCall(phoneNumber, uuid) {
            return __awaiter(this, void 0, void 0, function* () {
                try {
                    const callId = uuid || this.params.getUuid();
                    const res = yield RTCPeerConnectionFactory.outbound(this, callId, this.getLogin(), phoneNumber);
                    const pc = res[0];
                    const channel = res[1];
                    const call = new Call(callId, this, phoneNumber, this.getLogin(), pc, channel, 'outbound');
                    call.pushState(ZiwoEventType.Requesting);
                    call.pushState(ZiwoEventType.Trying);
                    return call;
                }
                catch (e) {
                    ZiwoEvent.error(ZiwoErrorCode.CannotCreateCall, e);
                    console.warn('failed to created call', e);
                }
            });
        }
        /**
         * Perform an attach query
         */
        attach(callId, phoneNumber, sdp) {
            try {
                this.send(this.params.attach(this.sessid, callId, this.getLogin(), phoneNumber, sdp));
                const c = this.calls.find(x => x.callId === callId);
                if (c) {
                    c.pushState(ZiwoEventType.Active);
                }
            }
            catch (e) {
                ZiwoEvent.error(ZiwoErrorCode.CannotCreateCall, e);
            }
        }
        /**
         * Answer a call
         */
        answerCall(callId, phoneNumber, sdp) {
            try {
                this.calls.forEach(x => {
                    if (x.callId !== callId) {
                        x.hold();
                    }
                });
                this.send(this.params.answerCall(this.sessid, callId, this.getLogin(), phoneNumber, sdp));
                const c = this.calls.find(x => x.callId === callId);
                if (c) {
                    c.pushState(ZiwoEventType.Active);
                }
            }
            catch (e) {
                ZiwoEvent.error(ZiwoErrorCode.MissingCall, e);
            }
        }
        /**
         * Hang up a specific call
         */
        hangupCall(callId, phoneNumber, reason = VertoByeReason.NORMAL_CLEARING) {
            this.send(this.params.hangupCall(this.sessid, callId, this.getLogin(), phoneNumber, reason));
            this.purgeAndDestroyCall(callId);
        }
        /**
         * Hold a specific call
         */
        holdCall(callId, phoneNumber) {
            this.send(this.params.setState(this.sessid, callId, this.getLogin(), phoneNumber, VertoState.Hold));
        }
        /**
         * Hang up a specific call
         */
        unholdCall(callId, phoneNumber) {
            this.send(this.params.setState(this.sessid, callId, this.getLogin(), phoneNumber, VertoState.Unhold));
        }
        blindTransfer(transferTo, callId, phoneNumber) {
            this.send(this.params.transfer(this.sessid, callId, this.getLogin(), phoneNumber, transferTo));
        }
        disconnect() {
            if (this.socket) {
                this.socket.close();
            }
        }
        restartSocket() {
            if (this.socket) {
                this.socket.close();
                delete this.socket;
            }
            this.openSocket(this.connectedAgent.webRtc.socket);
        }
        /**
         * Purge a specific call
         */
        purgeCall(callId) {
            const call = this.calls.find(x => x.callId === callId);
            if (call) {
                call.pushState(ZiwoEventType.Purge);
            }
        }
        /**
         * Destroy a specific call.
         */
        destroyCall(callId) {
            const callIndex = this.calls.findIndex(x => x.callId === callId);
            if (callIndex === -1) {
                return;
            }
            this.calls[callIndex].pushState(ZiwoEventType.Destroy);
            this.cleaner.destroyCall(this.calls[callIndex]);
            this.calls.splice(callIndex, 1);
        }
        /**
         * Purge & Destroy a specific call.
         */
        purgeAndDestroyCall(callId) {
            this.purgeCall(callId);
            this.destroyCall(callId);
        }
        /**
         * DTFM send a char to current call
         */
        dtmf(callId, phoneNumber, char) {
            this.send(this.params.dtmf(this.sessid, callId, this.getLogin(), phoneNumber, char));
        }
        /**
         * Send data to socket and log in case of debug
         */
        send(data) {
            if (this.debug) {
                console.log('Write message > ', data);
            }
            if (!this.socket) {
                return;
            }
            this.socket.send(JSON.stringify(data));
        }
        /**
         * login log the agent in the newly created socket
         */
        login(agentPosition) {
            this.position = agentPosition;
            return new Promise((onRes, onErr) => {
                if (!this.socket) {
                    return onErr();
                }
                this.sessid = VertoSession.get();
                this.send(this.params.login(this.sessid, agentPosition.name, agentPosition.password));
            });
        }
        /**
         * openSocket should be called directly after the constructor
         * It initializate the socket and set the handlers
         */
        openSocket(socketUrl) {
            return new Promise((onRes, onErr) => {
                this.socket = new WebSocket(socketUrl);
                this.socket.onclose = () => {
                    ZiwoEvent.emit(ZiwoEventType.Disconnected, { message: 'Socket closed' });
                    if (this.debug) {
                        console.log('Socket closed. now disconnected');
                    }
                };
                this.socket.onerror = (e) => {
                    this.disconnect();
                    this.socket = undefined;
                    ZiwoEvent.emit(ZiwoEventType.Disconnected, { message: 'Socket error' });
                    if (this.debug) {
                        console.warn('Socket error', e);
                    }
                };
                this.socket.onopen = () => {
                    if (this.debug) {
                        console.log('Socket opened');
                    }
                    // clear.prepareUnloadEvent makes sure we clear the calls properly when user closes the tab
                    this.cleaner.prepareUnloadEvent();
                    onRes();
                };
                this.socket.onmessage = (msg) => {
                    var _a;
                    try {
                        const data = JSON.parse(msg.data);
                        if (!this.isJsonRpcValid) {
                            ZiwoEvent.error(ZiwoErrorCode.ProtocolError, data);
                            throw new Error('Message is not a valid format');
                        }
                        if (data.error && data.error.code === -32000) {
                            (_a = this.socket) === null || _a === void 0 ? void 0 : _a.close();
                            return ZiwoEvent.emit(ZiwoEventType.Disconnected, { message: 'Duplicate connection' });
                        }
                        if (data.error && data.error.code === -32003) {
                            return;
                        }
                        const callId = data.params && data.params.callID ? data.params.callID :
                            (data.result && data.result.callID ? data.result.callID : undefined);
                        const relatedCall = callId ? this.calls.find(c => c.callId === callId) : undefined;
                        this.orchestrator.onInput(data, relatedCall);
                    }
                    catch (err) {
                        ZiwoEvent.error(ZiwoErrorCode.ProtocolError, err);
                        if (this.debug) {
                            console.warn('Invalid incoming message', err);
                        }
                    }
                };
            });
        }
        /**
         * Concat position to return the login used in Json RTC request
         */
        getLogin() {
            var _a, _b;
            return `${(_a = this.position) === null || _a === void 0 ? void 0 : _a.name}@${(_b = this.position) === null || _b === void 0 ? void 0 : _b.hostname}`;
        }
        ensureMediaChannelIsValid() {
            return __awaiter(this, void 0, void 0, function* () {
                const channel = yield this.io.getChannel();
                if (!channel || !channel.stream) {
                    ZiwoEvent.error(ZiwoErrorCode.MediaError, MESSAGES.MEDIA_ERROR);
                    return false;
                }
                return true;
            });
        }
        /**
         * Validate the JSON RPC headersx
         */
        isJsonRpcValid(data) {
            return typeof data === 'object'
                && 'jsonrpc' in data
                && data.jsonrpc === '2.0';
        }
    }

    class MediaChannel {
        constructor(stream) {
            this.stream = stream;
            this.audioContext = this.getAudioContext();
        }
        static getUserMediaAsChannel(io) {
            return new Promise((onRes, onErr) => {
                try {
                    const constraints = io.input ? { audio: { deviceId: io.input.deviceId } } : { audio: true };
                    navigator.mediaDevices.getUserMedia(constraints).then((stream) => {
                        onRes(new MediaChannel(stream));
                    }).catch(e => {
                        ZiwoEvent.error(ZiwoErrorCode.DevicesError, 'No devices available');
                    });
                }
                catch (e) {
                    onErr(e);
                }
            });
        }
        startMicrophone() {
            // see https://dvcs.w3.org/hg/audio/raw-file/tip/webaudio/specification.html#BiquadFilterNode-section
            const filterNode = this.audioContext.createBiquadFilter();
            filterNode.type = 'highpass';
            // cutoff frequency: for highpass, audio is attenuated below this frequency
            filterNode.frequency.value = 10000;
            // create a gain node (to change audio volume)
            const gainNode = this.audioContext.createGain();
            // default is 1 (no change); less than 1 means audio is attenuated and vice versa
            gainNode.gain.value = 0.5;
            const source = this.audioContext.createMediaStreamSource(this.stream);
            this.microphone = {
                filterNode,
                gainNode,
                source,
            };
        }
        bindVideo(el) {
            el.srcObject = this.stream;
        }
        getAudioContext() {
            let audioContext;
            if (typeof AudioContext === 'function') {
                audioContext = new AudioContext();
            }
            else {
                throw new Error('Web audio not supported');
            }
            return audioContext;
        }
    }

    var DeviceKind;
    (function (DeviceKind) {
        DeviceKind["VideoInput"] = "videoinput";
        DeviceKind["AudioInput"] = "audioinput";
        DeviceKind["AudioOutput"] = "audiooutput";
    })(DeviceKind || (DeviceKind = {}));
    /**
     * IO Service allow your to quickly manager your inputs and outputs
     */
    class IOService {
        constructor(calls) {
            this.volume = 100;
            // public channel?:MediaChannel;
            this.inputs = [];
            this.outputs = [];
            this.onDevicesUpdatedListeners = [];
            this.calls = calls;
            this.load().then(e => {
                if (this.inputs.length > 0) {
                    this.useDefaultInput();
                }
                else {
                    ZiwoEvent.error(ZiwoErrorCode.DevicesErrorNoInput, e);
                }
                if (this.outputs.length > 0) {
                    this.useDefaultOutput();
                }
                else {
                    ZiwoEvent.error(ZiwoErrorCode.DevicesErrorNoOutout, e);
                }
                this.emitDevicesUpdatedListeners(true, true);
            }).catch(e => {
                ZiwoEvent.error(ZiwoErrorCode.DevicesError, e);
            });
            this.listenForDevicesUpdate();
        }
        onDevicesUpdated(fn) {
            this.onDevicesUpdatedListeners.push(fn);
        }
        meetsRequirement() {
            return this.inputs.length > 0 && this.outputs.length > 0;
        }
        useDefaultInput() {
            this.useInput(this.inputs[0], false);
        }
        useDefaultOutput() {
            this.useOutput(this.outputs[0]);
        }
        getChannel() {
            return __awaiter(this, void 0, void 0, function* () {
                return new Promise((onRes, onErr) => {
                    if (!this.input) {
                        if (this.inputs && this.inputs.length > 0) {
                            this.useDefaultInput();
                        }
                        else {
                            onRes(undefined);
                        }
                    }
                    navigator.mediaDevices.getUserMedia({
                        audio: { deviceId: this.input.deviceId }
                    }).then((stream) => {
                        onRes(new MediaChannel(stream));
                    }).then().catch(() => {
                        onRes(undefined);
                    });
                });
            });
        }
        useInput(device, withRetryIfFailed = true) {
            return __awaiter(this, void 0, void 0, function* () {
                this.input = device;
                ZiwoEvent.emit(ZiwoEventType.InputChanged, { device: device });
                const channel = yield this.getChannel();
                this.calls.forEach(c => {
                    try {
                        c.rtcPeerConnection.getSenders().forEach(sender => {
                            if (sender.track && sender.track.kind === 'audio') {
                                sender.replaceTrack(channel === null || channel === void 0 ? void 0 : channel.stream.getAudioTracks()[0]);
                            }
                        });
                        if (channel) {
                            c.channel = channel;
                        }
                    }
                    catch (_a) {
                        console.warn(`fail to input rebind for ${c.callId}`);
                    }
                });
            });
        }
        useOutput(device) {
            return __awaiter(this, void 0, void 0, function* () {
                this.output = device;
                ZiwoEvent.emit(ZiwoEventType.OutputChanged, { device: device });
                window.setTimeout(() => {
                    this.calls.forEach(c => {
                        try {
                            const t = document.getElementById(`media-peer-${c.callId}`);
                            t.setSinkId(this.output.deviceId)
                                .then(() => {
                                var _a;
                                console.log(`Success, audio output device attached: ${(_a = this.output) === null || _a === void 0 ? void 0 : _a.deviceId}`);
                            })
                                .catch((error) => {
                                let errorMessage = error;
                                if (error.name === 'SecurityError') {
                                    errorMessage = `You need to use HTTPS for selecting audio output device: ${error}`;
                                }
                                console.error(errorMessage);
                            });
                        }
                        catch (_a) {
                            console.warn(`fail to output rebind for ${c.callId}`);
                        }
                    });
                });
            });
        }
        /**
         * return all the available input medias
         */
        getInputs() {
            return this.inputs;
        }
        /**
         * return all the available output medias
         */
        getOutputs() {
            return this.outputs;
        }
        setVolume(vol) {
            if (vol < 0) {
                vol = 0;
            }
            if (vol > 100) {
                vol = 100;
            }
            this.volume = vol;
        }
        load() {
            return new Promise((ok, err) => {
                navigator.mediaDevices.enumerateDevices().then((devices) => {
                    this.getDevices(devices);
                    ok();
                }).catch(e => err(e));
            });
        }
        emitDevicesUpdatedListeners(inputChanged, outputChanged) {
            this.onDevicesUpdatedListeners.forEach(f => f(inputChanged, outputChanged));
        }
        listenForDevicesUpdate() {
            if (!navigator || !navigator.mediaDevices) {
                return;
            }
            navigator.mediaDevices.ondevicechange = () => {
                this.load().then(() => {
                    this.emitDevicesUpdatedListeners(this.onInputListUpdated(), this.onOutputlistUpdated());
                });
            };
        }
        onInputListUpdated() {
            if (this.inputs.length === 0) {
                ZiwoEvent.error(ZiwoErrorCode.DevicesErrorNoInput, 'no input available');
                return false;
            }
            if (!this.input) {
                this.useDefaultInput();
                return true;
            }
            if (this.inputs.findIndex(i => { var _a; return i.deviceId === ((_a = this.input) === null || _a === void 0 ? void 0 : _a.deviceId); }) === -1) {
                // currently used device is not available anymore -
                this.useDefaultInput();
                return true;
            }
            return false;
        }
        onOutputlistUpdated() {
            if (this.outputs.length === 0) {
                ZiwoEvent.error(ZiwoErrorCode.DevicesErrorNoOutout, 'no output available');
                return false;
            }
            if (!this.output) {
                this.useDefaultOutput();
                return true;
            }
            if (this.outputs.findIndex(o => { var _a; return o.deviceId === ((_a = this.output) === null || _a === void 0 ? void 0 : _a.deviceId); }) === -1) {
                // currently used device is not available anymore -
                this.useDefaultOutput();
                return true;
            }
            return false;
        }
        getDevices(devices) {
            if (!devices) {
                return;
            }
            this.inputs.splice(0, this.inputs.length);
            this.outputs.splice(0, this.outputs.length);
            devices.forEach((device) => {
                switch (device.kind) {
                    case DeviceKind.VideoInput:
                        // We do not do support video
                        break;
                    case DeviceKind.AudioInput:
                        this.inputs.push({
                            label: device.label,
                            deviceId: device.deviceId,
                            groupId: device.groupId,
                        });
                        break;
                    case DeviceKind.AudioOutput:
                        this.outputs.push({
                            label: device.label,
                            deviceId: device.deviceId,
                            groupId: device.groupId,
                        });
                        break;
                }
            });
        }
    }

    /**
     * Ziwo Client allow your to setup the environment.
     * It will setup the WebRTC, open the WebSocket and do the required authentications
     *
     * See README#Ziwo Client to see how to instanciate a new client.
     * Make sure to wait for `connected` event before doing further action.
     *
     * Once the client is instancied and you received the `connected` event, Ziwo is ready to be used
     * and you can start a call by using `startCall(phoneNumber:string)` or simply wait for events to proc.
     */
    class ZiwoClient {
        constructor(options) {
            this.calls = [];
            this.options = options;
            this.debug = options.debug || false;
            if (options.useGoogleStun !== true) {
                this.optOutGoogleStunServer()
                    .then(ss => console.log('using stuns > ', ss))
                    .catch(err => console.log('using default Google Stuns'));
            }
            this.apiService = new ApiService(options.contactCenterName);
            this.io = new IOService(this.calls);
            this.verto = new Verto(this.calls, this.debug, options.mediaTag, this.io);
            if (options.autoConnect) {
                this.connect().then(r => {
                }).catch(err => { throw err; });
            }
        }
        restart(options) {
            // Drop all
            this.verto.disconnect();
            this.options = options;
            this.debug = options.debug || false;
            this.apiService = new ApiService(options.contactCenterName);
            this.io = new IOService(this.calls);
            this.verto = new Verto(this.calls, this.debug, options.mediaTag, this.io);
        }
        /**
         * connect authenticate the user over Ziwo & our communication socket
         * This function is required before proceeding with calls
         */
        connect() {
            return new Promise((onRes, onErr) => {
                AuthenticationService.authenticate(this.apiService, this.options.credentials)
                    .then(res => {
                    this.connectedAgent = res;
                    this.verto.connectAgent(this.connectedAgent, this.options.contactCenterName);
                    onRes();
                }).catch(err => onErr(err));
            });
        }
        /**
         * Disconnect user from our socket and stop the protocol
         */
        disconnect() {
            return new Promise((onRes, onErr) => {
                AuthenticationService.logout(this.apiService).then(((r) => {
                    this.verto.disconnect();
                })).catch(c => { });
            });
        }
        restartSocket() {
            return this.verto.restartSocket();
        }
        /**
         * Add a callback function for all events
         * Can be used instead of `addEventListener`
         * NoteL Event thrown through this support
         * does not include the `ziwo` suffix nor the `_jorel-dialog-state` prefix
         */
        addListener(func) {
            return ZiwoEvent.subscribe(func);
        }
        /**
         * Start a phone call with the external phone number provided and return an instance of the Call
         * Note: the call's instance will also be provided in all the events
         */
        startCall(phoneNumber) {
            return __awaiter(this, void 0, void 0, function* () {
                const call = yield this.verto.startCall(phoneNumber);
                if (!call) {
                    return undefined;
                }
                this.calls.push(call);
                return call;
            });
        }
        /**
         * Start a call using click2call
         * return the call ID if the call is successful or undefined if an issue occured
         */
        startClick2Call(phoneNumber, roaming = false) {
            return new Promise((onRes, onErr) => {
                this.apiService.post(`${this.apiService.endpoints.click2Call}/${encodeURIComponent(phoneNumber)}`, {
                    roamingOnly: roaming,
                }).then(ok => onRes(ok.content.callID)).catch(e => onErr(e));
            });
        }
        answerCall() {
            /**
             * Start a phone call with the external phone number provided and return an instance of the Call
             * Note: the call's instance will also be provided in all the events
             */
        }
        /**
         * Opt out of Google Stun
         */
        optOutGoogleStunServer() {
            return new Promise((onRes, onErr) => {
                window.fetch('https://stun.ziwo.io', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                }).then(stunsRaw => {
                    stunsRaw.json().then(data => {
                        try {
                            RTCPeerConnectionFactory.STUN_ICE_SERVER = data.stun.map((x) => `stun:${x}`);
                            onRes(RTCPeerConnectionFactory.STUN_ICE_SERVER);
                        }
                        catch (_a) {
                            onErr();
                        }
                    }).catch(_e => onErr());
                }).catch(_e => onErr());
            });
        }
    }

    exports.ZiwoClient = ZiwoClient;

    Object.defineProperty(exports, '__esModule', { value: true });

})));
//# sourceMappingURL=ziwo-core-front.umd.js.map

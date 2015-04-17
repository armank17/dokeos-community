/**
 * This file is the supplementary JavaScript functions for using together
 * with the Gong applet scripting interface (GASI).
 * In order to use GASI XML are transmitted between the Gong applet and
 * the scripting language. To make this step easier we provide a few
 * handy functions for making this communication so that there is no
 * need to construct the XML message by yourself.
 *
 * For more information on the Gong project please visit:
 *
 *    http://gong.ust.hk/
 * 
 * Released date of this version: 24 February 2011
 */

/** The namespace URI */
var GASI_NAMESPACE_URI = "http://gong.ust.hk/gasi10";

/** Whether use xml as the communication method */
var useXML = false;

function gongUseXML(use) {
    useXML = use;
}

/** Plays the currently selected voice message
 *  - gong      The Gong applet
 *  - startTime Start time
 *  - endTime   End time
 */
function gongPlay(gong, startTime, endTime) {
    if (gong == null || !gong.isActive()) return "";

    if (useXML) {
        var request = "<PlayMediaRequest xmlns=\"" + GASI_NAMESPACE_URI + "\">";
        request += "<MediaType>audio</MediaType>";
        if (startTime != null) request += "<StartTime>" + startTime + "</StartTime>";
        if (endTime != null) request += "<EndTime>" + endTime + "</EndTime>";
        request += "</PlayMediaRequest>";
        if (gong != null) {
            var response = gong.sendGongRequest(request);
            if (isFault(response)) return getFaultReason(response);
            return getParameter(response, "Time");
        }
    }
    else if (gong != null) return gong.sendGongRequest("PlayMedia", "audio", startTime, endTime);
}

/** Records a new voice message
 *  - gong      The Gong applet
 *  - duration  The maximum duration
 *  Return the actual maximum duration
 */
function gongRecord(gong, duration) {
    if (gong == null || !gong.isActive()) return "";

    if (useXML) {
        var request = "<RecordMediaRequest xmlns=\"" + GASI_NAMESPACE_URI + "\">";
        request += "<MediaType>audio</MediaType>";
        if (duration != null) request += "<Duration>" + duration + "</Duration>";
        request += "</RecordMediaRequest>";
        if (gong != null) {
            var response = gong.sendGongRequest(request);
            if (isFault(response)) return getFaultReason(response);
            return getParameter(response, "Duration");
        }
        return null;
    }
    else if (gong != null) return gong.sendGongRequest("RecordMedia", "audio", duration);
}

/** Pauses the currently playback
 *  - gong      The Gong applet
 *  Return the time when paused
 */
function gongPause(gong) {
    if (gong == null || !gong.isActive()) return "Gong Applet is not ready.";

    if (useXML) {
        var request = "<PauseMediaRequest xmlns=\"" + GASI_NAMESPACE_URI + "\">";
        request += "<MediaType>audio</MediaType>";
        request += "</PauseMediaRequest>";
        if (gong != null) {
            var response = gong.sendGongRequest(request);
            if (isFault(response)) return getFaultReason(response);
            return getParameter(response, "Time");
        }
        return null;
    }
    else if (gong != null) return gong.sendGongRequest("PauseMedia", "audio");
}

/** Stops the currently playback or recording
 *  - gong      The Gong applet
 *  Return the duration if it exists
 */
function gongStop(gong) {
    if (gong == null || !gong.isActive()) return "Gong Applet is not ready.";

    if (useXML) {
        var request = "<StopMediaRequest xmlns=\"" + GASI_NAMESPACE_URI + "\">";
        request += "<MediaType>audio</MediaType>";
        request += "</StopMediaRequest>";
        if (gong != null) {
            var response = gong.sendGongRequest(request);
            if (isFault(response)) return getFaultReason(response);
            return getParameter(response, "Duration");
        }
        return null;
    }
    else if (gong != null) return gong.sendGongRequest("StopMedia", "audio");
}

/** Clears the current recording
 *  - gong      The Gong applet
 */
function gongClear(gong) {
    if (gong == null || !gong.isActive()) return "Gong Applet is not ready.";

    if (useXML) {
        var request = "<ClearMediaRequest xmlns=\"" + GASI_NAMESPACE_URI + "\">";
        request += "<MediaType>audio</MediaType>";
        request += "</ClearMediaRequest>";
        if (gong != null) {
            var response = gong.sendGongRequest(request);
            if (isFault(response)) return getFaultReason(response);
            return "";
        }
        return null;
    }
    else if (gong != null) return gong.sendGongRequest("ClearMedia", "audio");
}

/** Sets the time of the playback
 *  - gong      The Gong applet
 *  - time      The time
 */
function gongSetTime(gong, time) {
    if (gong == null || !gong.isActive()) return "Gong Applet is not ready.";

    if (useXML) {
        var request = "<SetMediaTimeRequest xmlns=\"" + GASI_NAMESPACE_URI + "\">";
        request += "<MediaType>audio</MediaType>";
        request += "<Time>" + time + "</Time>";
        request += "</SetMediaTimeRequest>";
        if (gong != null) {
            var response = gong.sendGongRequest(request);
            if (isFault(response)) return getFaultReason(response);
            return getParameter(response, "Time");
        }
    }
    else if (gong != null) return gong.sendGongRequest("SetMediaTime", "audio", time);
}

/** Gets the time of the playback
 *  - gong      The Gong applet
 *  Return the time
 */
function gongGetTime(gong) {
    if (gong == null || !gong.isActive()) return "Gong Applet is not ready.";

    if (useXML) {
        var request = "<GetMediaTimeRequest xmlns=\"" + GASI_NAMESPACE_URI + "\">";
        request += "<MediaType>audio</MediaType>";
        request += "</GetMediaTimeRequest>";
        if (gong != null) {
            var response = gong.sendGongRequest(request);
            if (isFault(response)) return getFaultReason(response);
            return getParameter(response, "Time");
        }
        return null;
    }
    else if (gong != null) return gong.sendGongRequest("GetMediaTime", "audio");
}

/** Sets the rate of the playback
 *  - gong      The Gong applet
 *  - rate      The playback rate
 */
function gongSetRate(gong, rate) {
    if (gong == null || !gong.isActive()) return "Gong Applet is not ready.";

    if (useXML) {
        var request = "<SetMediaRateRequest xmlns=\"" + GASI_NAMESPACE_URI + "\">";
        request += "<MediaType>audio</MediaType>";
        request += "<Rate>" + rate + "</Rate>";
        request += "</SetMediaRateRequest>";
        if (gong != null) {
            var response = gong.sendGongRequest(request);
            if (isFault(response)) return getFaultReason(response);
            return getParameter(response, "Rate");
        }
    }
    else if (gong != null) return gong.sendGongRequest("SetMediaRate", "audio", rate);
}

/** Gets the rate of the playback
 *  - gong      The Gong applet
 *  Return the rate
 */
function gongGetRate(gong) {
    if (gong == null || !gong.isActive()) return "Gong Applet is not ready.";

    if (useXML) {
        var request = "<GetMediaRateRequest xmlns=\"" + GASI_NAMESPACE_URI + "\">";
        request += "<MediaType>audio</MediaType>";
        request += "</GetMediaRateRequest>";
        if (gong != null) {
            var response = gong.sendGongRequest(request);
            if (isFault(response)) return getFaultReason(response);
            return getParameter(response, "Rate");
        }
        return null;
    }
    else if (gong != null) return gong.sendGongRequest("GetMediaRate", "audio");
}

/** Gets the status of the audio
 *  - gong      The Gong applet
 *  Return the status
 */
function gongGetStatus(gong) {
    if (gong == null || !gong.isActive()) return "Gong Applet is not ready.";

    if (useXML) {
        var request = "<GetMediaStatusRequest xmlns=\"" + GASI_NAMESPACE_URI + "\">";
        request += "<MediaType>audio</MediaType>";
        request += "</GetMediaStatusRequest>";
        if (gong != null) {
            var response = gong.sendGongRequest(request);
            if (isFault(response)) return getFaultReason(response);
            return getParameter(response, "Status");
        }
        return null;
    }
    else if (gong != null) return gong.sendGongRequest("GetMediaStatus", "audio");
}

/** Gets the level of the audio
 *  - gong      The Gong applet
 *  Return the level
 */
function gongGetAudioLevel(gong) {
    if (gong == null || !gong.isActive()) return "Gong Applet is not ready.";

    if (useXML) {
        var request = "<GetAudioLevelRequest xmlns=\"" + GASI_NAMESPACE_URI + "\"/>";
        if (gong != null) {
            var response = gong.sendGongRequest(request);
            if (isFault(response)) return getFaultReason(response);
            return getParameter(response, "Level");
        }
        return null;
    }
    else if (gong != null) return gong.sendGongRequest("GetAudioLevel", "");
}

/** Moves to the previous message
 *  - gong      The Gong applet
 *  Return the message id
 */
function gongMoveToPrevMessage(gong) {
    if (gong == null || !gong.isActive()) return "Gong Applet is not ready.";

    if (useXML) {
        var request = "<MoveToPrevMessageRequest xmlns=\"" + GASI_NAMESPACE_URI + "\"/>";
        if (gong != null) {
            var response = gong.sendGongRequest(request);
            if (isFault(response)) return getFaultReason(response);
            return getParameter(response, "MessageId");
        }
        return null;
    }
    else if (gong != null) return gong.sendGongRequest("MoveToPrevMessage", "");
}

/** Moves to the next message
 *  - gong      The Gong applet
 *  Return the message id
 */
function gongMoveToNextMessage(gong) {
    if (gong == null || !gong.isActive()) return "Gong Applet is not ready.";

    if (useXML) {
        var request = "<MoveToNextMessageRequest xmlns=\"" + GASI_NAMESPACE_URI + "\"/>";
        if (gong != null) {
            var response = gong.sendGongRequest(request);
            if (isFault(response)) return getFaultReason(response);
            return getParameter(response, "MessageId");
        }
        return null;
    }
    else if (gong != null) return gong.sendGongRequest("MoveToNextMessage", "");
}

/** Selects message
 *  - gong      The Gong applet
 *  - id        The message id
 */
function gongSelectMessage(gong, id) {
    if (gong == null || !gong.isActive()) return "Gong Applet is not ready.";

    if (useXML) {
        var request = "<SelectMessageRequest xmlns=\"" + GASI_NAMESPACE_URI + "\">";
        request += "<MessageId>" + id + "</MessageId>";
        request += "</SelectMessageRequest>";
        if (gong != null) {
            var response = gong.sendGongRequest(request);
            if (isFault(response)) return getFaultReason(response);
            return getParameter(response, "MessageId");
        }
    }
    else if (gong != null) return gong.sendGongRequest("SelectMessage", id);
}

/** Gets the currently selected message id
 *  - gong      The Gong applet
 *  Return the message id
 */
function gongGetCurrentMessageId(gong) {
    if (gong == null || !gong.isActive()) return "Gong Applet is not ready.";

    if (useXML) {
        var request = "<GetCurrentMessageIdRequest xmlns=\"" + GASI_NAMESPACE_URI + "\"/>";
        if (gong != null) {
            var response = gong.sendGongRequest(request);
            if (isFault(response)) return getFaultReason(response);
            return getParameter(response, "MessageId");
        }
        return null;
    }
    else if (gong != null) return gong.sendGongRequest("GetCurrentMessageId", "");
}

/** Searches message
 *  - gong      The Gong applet
 *  - finder    The finder object
 *  Return the message id
 */
function gongSearchMessage(gong, finder) {
    if (gong == null || !gong.isActive()) return "Gong Applet is not ready.";

    if (useXML) {
        var request = "<SearchMessageRequest xmlns=\"" + GASI_NAMESPACE_URI + "\">";
        request += finder.createElements();
        request += "</SearchMessageRequest>";
        if (gong != null) {
            var response = gong.sendGongRequest(request);
            if (isFault(response)) return getFaultReason(response);
            return getParameter(response, "MessageId");
        }
        return null;
    }
    else if (gong != null) return gong.sendGongRequest("SearchMessage", finder.text, finder.toParams());
}

/** Gets the message
 *  - gong      The Gong applet
 *  - id        The message id
 *  Return the message
 */
function gongGetMessage(gong, id) {
    if (gong == null || !gong.isActive()) return "Gong Applet is not ready.";

    if (useXML) {
        var request = "<GetMessageRequest xmlns=\"" + GASI_NAMESPACE_URI + "\">";
        request += "<MessageId>" + id + "</MessageId>";
        request += "</GetMessageRequest>";
        if (gong != null) {
            var response = gong.sendGongRequest(request);
            if (isFault(response)) return getFaultReason(response);
            return getParameter(response, "Message");
        }
        return null;
    }
    else if (gong != null) return gong.sendGongRequest("GetMessage", id);
}

/** Gets the message content
 *  - gong      The Gong applet
 *  - id        The message id
 *  - type      The content type
 *  Return the message content
 */
function gongGetMessageContent(gong, id, type) {
    if (gong == null || !gong.isActive()) return "Gong Applet is not ready.";

    if (useXML) {
        var request = "<GetMessageContentRequest xmlns=\"" + GASI_NAMESPACE_URI + "\">";
        request += "<MessageId>" + id + "</MessageId>";
        request += "<ContentType>" + type + "</ContentType>";
        request += "</GetMessageContentRequest>";
        if (gong != null) {
            var response = gong.sendGongRequest(request);
            if (isFault(response)) return getFaultReason(response);
            return getParameter(response, "Content");
        }
        return null;
    }
    else if (gong != null) return gong.sendGongRequest("GetMessageContent", id, type);
}

/** Posts the message on the board
 *  - gong      The Gong applet
 *  - parent    The parent message id
 *  - name      The author
 *  - subject   The subject
 *  - content   The content
 *  Return the message id
 */
function gongPostMessage(gong, parent, name, subject, content) {
    if (gong == null || !gong.isActive()) return "Gong Applet is not ready.";

    if (useXML) {
        var request = "<PostMessageRequest xmlns=\"" + GASI_NAMESPACE_URI + "\">";
        request += "<Subject>" + subject + "</Subject>";
        if (content != null) request += "<Content>" + content + "</Content>";
        if (parent != null) request += "<Parent>" + parent + "</Parent>";
        request += "</PostMessageRequest>";
        if (gong != null) {
            var response = gong.sendGongRequest(request);
            if (isFault(response)) return getFaultReason(response);
            return getParameter(response, "MessageId");
        }
        return null;
    }
    else if (gong != null)
        return gong.sendGongRequest("PostMessage", parent, name, subject, content);
}

/** Saves the message to the harddisk
 *  - gong      The Gong applet
 *  - parent    The parent message id
 *  - type      The file type
 *  - filename  The filename
 *  - path      The path
 *  Return the filename
 */
function gongSaveMessage(gong, type, filename, path) {
    if (gong == null || !gong.isActive()) return "Gong Applet is not ready.";

    if (useXML) {
        var request = "<SaveMessageRequest xmlns=\"" + GASI_NAMESPACE_URI + "\">";
        request += "<Type>" + type + "</Type>";
        request += "<Filename>" + filename + "</Filename>";
        if (path != null) request += "<Path>" + path + "</Path>";
        request += "</SaveMessageRequest>";
        if (gong != null) {
            var response = gong.sendGongRequest(request);
            if (isFault(response)) return getFaultReason(response);
            return getParameter(response, "Filename");
        }
        return null;
    }
    else if (gong != null)
        return gong.sendGongRequest("SaveMessage", type, filename, path);
}

/** Gets the currently playing token
 *  - gong      The Gong applet
 *  Return the current token
 */
function gongGetCurrentToken(gong) {
    if (gong == null || !gong.isActive()) return "Gong Applet is not ready.";

    if (useXML) {
        var request = "<GetCurrentTokenRequest xmlns=\"" + GASI_NAMESPACE_URI + "\"/>";
        if (gong != null) {
            var response = gong.sendGongRequest(request);
            if (isFault(response)) return getFaultReason(response);
            return getParameter(response, "Token");
        }
        return null;
    }
    else if (gong != null) return gong.sendGongRequest("GetCurrentToken", "");
}

/** Gets the board name
 *  - gong      The Gong applet
 *  Return the board name
 */
function gongGetBoardName(gong) {
    if (gong == null || !gong.isActive()) return "Gong Applet is not ready.";

    if (useXML) {
        var request = "<GetBoardNameRequest xmlns=\"" + GASI_NAMESPACE_URI + "\"/>";
        if (gong != null) {
            var response = gong.sendGongRequest(request);
            if (isFault(response)) return getFaultReason(response);
            return getParameter(response, "Name");
        }
        return null;
    }
    else if (gong != null) return gong.sendGongRequest("GetBoardName", "");
}

/** Gets the board data
 *  - gong      The Gong applet
 *  Return the board data
 */
function gongGetBoardData(gong) {
    if (gong == null || !gong.isActive()) return "Gong Applet is not ready.";

    if (useXML) {
        var request = "<GetBoardDataRequest xmlns=\"" + GASI_NAMESPACE_URI + "\"/>";
        if (gong != null) {
            var response = gong.sendGongRequest(request);
            if (isFault(response)) return getFaultReason(response);
            return getParameter(response, "Board");
        }
        return null;
    }
    else if (gong != null) return gong.sendGongRequest("GetBoardData", "");
}

/** Gets the Gong version
 *  - gong      The Gong applet
 *  Return the version
 */
function gongGetVersion(gong) {
    if (gong == null || !gong.isActive()) return "Gong Applet is not ready.";

    if (useXML) {
        var request = "<GetVersionRequest xmlns=\"" + GASI_NAMESPACE_URI + "\"/>";
        if (gong != null) {
            var response = gong.sendGongRequest(request);
            if (isFault(response)) return getFaultReason(response);
            return getParameter(response, "Version");
        }
        return null;
    }
    else if (gong != null) return gong.sendGongRequest("GetVersion", "");
}

/** Determines if the response is a fault
 *  - response  The response
 *  Return true or false
 */
function isFault(response) {
    response = response + "";
    return (response.match(/<Fault.*?>/) != null);
}


/** Gets the fault reason
 *  - fault     The fault
 *  Return the reason of the fault
 */
function getFaultReason(fault) {
    return getParameter(fault, "Reason");
}

/** Gets message parameter
 *  - xmlDoc    The document
 *  - tagName   The tag name
 *  Return the parameter value or null if not found
 */
function getParameter(response, tagName) {
    response = response + "";
    var start = response.indexOf("<" + tagName + ">");
    if (start < 0) return null;
    start += tagName.length + 2;
    var end = response.indexOf("</" + tagName + ">");
    if (end < 0) return null;
    if (start > end) return null;
    return response.substring(start, end);
}

/** Constructor for the finder object */
function Finder(text) {
    this.text = text;
    this.caseSensitive = false;
    this.wholeWords = false;
    this.regex = false;
    this.targets = this.TARGET_SUBJECT | this.TARGET_CONTENT | this.TARGET_AUTHOR;
}

/** The constants for setting the targets of the finder */
Finder.prototype.TARGET_SUBJECT = 1;
Finder.prototype.TARGET_CONTENT = 2;
Finder.prototype.TARGET_AUTHOR = 4;

/** Creates the finder elements in the target
 *  - target    The target element
 */
Finder.prototype.createElements = function() {
    var output = "";
    output += "<Text>" + this.text + "</Text>";
    output += "<Options>";
    output += "<CaseSensitive>" + (this.caseSensitive? "true" : "false") + "</CaseSensitive>";
    output += "<WholeWords>" + (this.wholeWords? "true" : "false") + "</WholeWords>";
    output += "<Regex>" + (this.regex? "true" : "false") + "</Regex>";
    output += "<Targets>";
    output += "<Author>" + ((this.targets & this.TARGET_AUTHOR > 0)? "true" : "false") + "</Author>";
    output += "<Subject>" + ((this.targets & this.TARGET_SUBJECT > 0)? "true" : "false") + "</Subject>";
    output += "<Content>" + ((this.targets & this.TARGET_CONTENT > 0)? "true" : "false") + "</Content>";
    output += "</Targets>";
    output += "</Options>";
    return output;
}

/** Creates the parameters of the finder
 */
Finder.prototype.toParams = function() {
    var output = "";
    output += (this.caseSensitive? "true" : "false");
    output += ";" + (this.wholeWords? "true" : "false");
    output += ";" + (this.regex? "true" : "false");
    output += ";" + ((this.targets & this.TARGET_AUTHOR > 0)? "true" : "false");
    output += ";" + ((this.targets & this.TARGET_SUBJECT > 0)? "true" : "false");
    output += ";" + ((this.targets & this.TARGET_CONTENT > 0)? "true" : "false");
    return output;
}

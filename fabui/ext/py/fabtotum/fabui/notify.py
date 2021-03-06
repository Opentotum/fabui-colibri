#!/bin/env python
# -*- coding: utf-8; -*-
#
# (c) 2016 FABtotum, http://www.fabtotum.com
#
# This file is part of FABUI.
#
# FABUI is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# FABUI is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with FABUI.  If not, see <http://www.gnu.org/licenses/>.

__authors__ = "Daniel Kesler"
__license__ = "GPL - https://opensource.org/licenses/GPL-3.0"
__version__ = "1.0"

# Import standard python module
import json
import time
import gettext
import logging
from threading import Event, Thread, RLock

# Import external modules
from ws4py.client.threadedclient import WebSocketClient

# Import internal modules
from fabtotum.fabui.config import ConfigService
#~ from fabtotum.database      import Database, timestamp2datetime
#~ from fabtotum.database.task import Task

# Set up message catalog access
tr = gettext.translation('notify', 'locale', fallback=True)
_ = tr.ugettext

class NotifyService(object):
    """
    Notification service. Handles all notification by sending the messages to
    an opened websocket and writing the same messages to a NOTIFY_FILE file as a 
    fallback in case websocket is not supported.
    """
    
    def __init__(self, WebSocket = None, notify_file = None, config = None):
        
        self.notify_lock = RLock()
        
        if not config:
            self.config = ConfigService()
        else:
            self.config = config
        
        if not notify_file:
            notify_file = self.config.get('general', 'notify_file')
        
        if not WebSocket:
            SOCKET_HOST         = self.config.get('socket', 'host')
            SOCKET_PORT         = self.config.get('socket', 'port')
            self.ws = WebSocketClient('ws://'+SOCKET_HOST +':'+SOCKET_PORT+'/')
            self.ws.connect();
        else:
            self.ws = WebSocket
        
        self.notify_file = notify_file
        
        self.backtrack = int(self.config.get('notify', 'backtrack', 30))
        self.event_id = 0
        self.events = []
            
    def notify(self, event_type, event_data):
        
        with self.notify_lock:
            self.__add_event(event_type, event_data)
            self.__send_message(event_type, event_data)
    
    def get_last(self):
        if self.events:
            return self.events[-1]
        return None
    
    def __add_event(self, event_type, event_data):
        """
        Add a new event to the event list and write the list to NOTIFY_FILE.
        """
        self.event_id += 1 # Increment the event ID number
        
        event = {'id': self.event_id, 'type': event_type, 'data':event_data}
        
        if len(self.events) >= self.backtrack:
            self.events = self.events[1:] + [event]
        else:
            self.events.append(event)
        
        wrapper = {
            'events' : self.events,
            'last_event' : event
        }
        
        with open(self.notify_file, 'w') as f:
            f.write( json.dumps(wrapper) )
    
    def __send_message(self, type, data):
        """
        Send message to WebSocket server.
        
        :param type: Message type
        :param data: Message data
        :type type: string
        :type data: string
        """
        message = {'type': type, 'data':data}
        self.ws.send(json.dumps(message))

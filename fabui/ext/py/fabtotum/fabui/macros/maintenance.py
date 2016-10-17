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

__authors__ = "Marco Rizzuto, Daniel Kesler"
__license__ = "GPL - https://opensource.org/licenses/GPL-3.0"
__version__ = "1.0"


# Import standard python module
import gettext

# Import external modules

# Import internal modules


# Set up message catalog access
tr = gettext.translation('gmacro', 'locale', fallback=True)
_ = tr.ugettext

def extrude(app, args):
    filamentToExtrude = float(args[0])
    units_e = app.config.get('settings', 'e')
    app.macro("M92 E{0}".format(units_e), "ok", 2, _("Setting extruder mode") )
    app.macro("M302",  "ok", 1,    _("Allowing cold extrusion") )
    app.macro("G91",   "ok", 1,    _("Set rel position") )
    app.macro("G0 E{0} F400".format(filamentToExtrude),    "ok", 100,    _("Extruding...") )
    app.macro("M400",       "ok", 200,    _("Waiting for all moves to finish"), verbose=False)
    
def change_step(app, args):
    new_step = float(args[0])
    app.macro("M92 E{0}".format(new_step),  "ok", 1,   _("Setting extruder mode") )
    app.macro("M500",                       None, 1,   _("Writing settings to eeprom") )

def pre_unload_spool(app, args = None):        
    app.macro("M104 S190",  "ok", 5,    _("Pre-Heating Nozzle...") )
    app.macro("M109 S190",  None, 400,  _("Waiting for nozzle to reach temperature...") ) #heating and waiting.
    
def unload_spool(app, args = None):
    units_e = app.config.get('settings', 'e')
    
    app.trace( _("Unloading Spool : Procedure Started.") )
    app.macro("G90",                "ok", 10,   _("Set abs position"), verbose=False)
    app.macro("M302 S0",            "ok", 10,   _("Extrusion prevention disabled"), verbose=False)
    app.macro("G27",                "ok", 100,  _("Zeroing Z axis"), verbose=False)
    app.macro("G0 Z150 F10000",     "ok", 10,   _("Moving to safe zone"), verbose=False) #right top corner Z=150mm
    app.macro("G91",                "ok", 2,    _("Set rel position"), verbose=False)
    app.macro("G92 E0",             "ok", 5,    _("Set extruder to zero"), verbose=False)
    app.macro("M92 E{0}".format(units_e), "ok", 30,   _("Setting extruder mode"), verbose=False)

    
    app.macro("M300",               "ok", 2,    _("<b>Start Pulling!</b>"), verbose=False)
    app.macro("M400",               "ok", 100,  _("Wait for move to finish"), verbose=False)
    app.trace( _("<b>Start Pulling!</b>") )
    app.macro("G0 E-800 F550",      "ok", 10,   _("Expelling filament") )
    app.macro("M400",               "ok", 300,  _("Wait for move to finish"), verbose=False)
    app.macro("G0 E-200 F550",      "ok", 10,   _("Expelling filament"), verbose=False)
    app.macro("M400",               "ok", 300,  _("Wait for move to finish"), verbose=False)
    
    app.macro("M104 S0",            "ok", 1,    _("Turning off heater") )
    app.macro("M302 S170",          "ok", 10,   _("Extrusion prevention enabled"), verbose=False)
    
def load_spool(app, args = None):
    units_e = app.config.get('settings', 'e')
    
    app.trace( _("Loading Spool : Procedure Started.") )
    app.macro("G90",                "ok", 2,    _("Set abs position"), verbose=False)
    app.macro("G27",                "ok", 100,  _("Zeroing Z axis"), verbose=False)
    app.macro("G0 Z150 F10000",     "ok", 10,   _("Moving to Safe Zone"), verbose=False)
    app.macro("M302 S0",            "ok", 5,    _("Enabling Cold extrusion"), verbose=False)
    app.macro("G91",                "ok", 2,    _("Set relative position"), verbose=False)
    app.macro("G92 E0",             "ok", 5,    _("Setting extruder position to 0"), verbose=False)
    app.macro("M92 E{0}".format(units_e), "ok", 5,    _("Setting extruder mode"), verbose=False)
    app.macro("M104 S190",          "ok", 5,    _("Pre-Heating Nozzle. Get ready to push...") ) #heating and waiting.
    app.macro("M300",               "ok", 5,    _("<b>Start pushing!</b>") )

    app.macro("G0 E110 F500",       "ok", 1,    _("Loading filament") )
    app.macro("G0 E660 F700",       "ok", 1,    _("Loading filament (fast)") )
    app.macro("M109 S210",          None, 400,  _("Waiting to get to temperature...") ) #heating and waiting.
    app.macro("M400",               "ok", 300,  _("Wait for move to finish"), verbose=False)
    app.macro("G0 E100 F200",       "ok", 1,    _("Entering the hotend (slow)") )
    app.macro("M400",               "ok", 300,  _("Wait for move to finish"), verbose=False)

    app.macro("M104 S0",            "ok", 1,    _("Turning off heater") )
    app.macro("M302 S170",          "ok", 1,    _("Disabling Cold Extrusion Prevention"), verbose=False)

def manual_bed_leveling(app, apps = None):
    
    app.trace( _("Manual bed leveling started.") )
    
    skip_homing = False
    PROBE_SECURE_OFFSET = 15.0
    probe_height    = 50.0
    
    try:
        safety_door = app.config.get('settings', 'safety')['door']
    except KeyError:
        safety_door = 0

    if safety_door == 1:
        app.macro("M741",           "TRIGGERED", 2, _("Front panel door control") )
    
    zprobe = app.config.get('settings', 'zprobe')
    zprobe_disabled = (zprobe['enable'] == 0)
    zmax_home_pos   = float(zprobe['zmax'])
    
    probe_length = 50.0
    
    reply = app.macro("M503", "ok", 2, _("Get proble length"), verbose=False)
    for line in reply:
        if line.startswith("echo:Z Probe Length:"):
            probe_length = abs(float(line.split("Z Probe Length: ")[1]))
            probe_height = (probe_length + 1) + PROBE_SECURE_OFFSET

    try:
        app.macro("M744",  "TRIGGERED",    2,  _("Milling bed side up"), verbose=False)
    except:
        app.trace(_("Milling bed side up"))
        try:
            milling_offset = float(app.config.get('settings', 'milling')['layer_offset'])
            app.trace("Milling sacrificial layer thickness: "+str(milling_offset))
            probe_height += milling_offset
        except KeyError:
            app.trace("Milling sacrificial layer thickness not configured - assuming zero")                

    app.macro("M402",  "ok",   2,  _("Retracting Probe (safety)"), warning=True, verbose=False)
    app.macro("G90",   "ok",   5,  _("Setting abs mode"),          verbose=False)

    if not skip_homing:
        app.macro("G27",           "ok",   100,    _("Homing Z - Fast") )
        app.macro("G92 Z241.2",    "ok",   5,      _("Setting correct Z"), verbose=False)
        app.macro("M402",          "ok",   2,      _("Retracting Probe (safety)"), verbose=False)

    app.macro("G0 Z{0} F5000".format(probe_height),    "ok",   5,  _("Moving to start Z height")) #mandatory!
    app.macro("M400",       "ok", 200,    _("Waiting for all moves to finish"), verbose=False )

    app.macro("M401",          "ok",   2,      _("Extend Probe"), verbose=False)

    return {'probe_height' : probe_height}

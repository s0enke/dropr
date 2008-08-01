#!/bin/sh
#
# dropr
#
# Copyright (c) 2008, by the dropr project https://www.dropr.org/
# All rights reserved.
#
# Redistribution and use in source and binary forms, with or without
# modification, are permitted provided that the following conditions
# are met:
#
#   * Redistributions of source code must retain the above copyright
#     notice, this list of conditions and the following disclaimer.
#
#   * Redistributions in binary form must reproduce the above copyright
#     notice, this list of conditions and the following disclaimer in
#     the documentation and/or other materials provided with the
#     distribution.
#
#   * Neither the name of dropr nor the names of its
#     contributors may be used to endorse or promote products derived
#     from this software without specific prior written permission.
#
# THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
# "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
# LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
# FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
# COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
# INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
# BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
# LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
# CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
# LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
# ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
# POSSIBILITY OF SUCH DAMAGE.
#
# @package    dropr
# @author     Soenke Ruempler <soenke@jimdo.com>
# @author     Boris Erdmann <boris@jimdo.com>
# @copyright  2007-2008 Soenke Ruempler, Boris Erdmann
# @license    http://www.opensource.org/licenses/bsd-license.php  BSD License

CWD="`pwd`"

INITSCRIPT="$CWD/dropr"
CONFIG="$CWD/dropr.cfg-default"

if [ -e "$CONFIG" ]
then
  . "$CONFIG"

  case "$1" in
    remove)
      /etc/init.d/dropr stop

      rm -f /etc/init.d/dropr
      rm -f /etc/default/dropr

      echo
      update-rc.d dropr remove
      echo
    ;;
    *)

      mkdir -p $SPOOLDIR/in
      mkdir -p $SPOOLDIR/proc
      mkdir -p $SPOOLDIR/sent

      chmod -R ug+rwx $SPOOLDIR
      chown -R www-data:www-data $SPOOLDIR

      ln -s $INITSCRIPT /etc/init.d/dropr
      cp $CONFIG /etc/default/dropr

      echo
      update-rc.d dropr defaults 99 01
      echo
    ;;
   esac

else
  echo "$CONFIG missing."
  echo "Could not install/remove."
fi

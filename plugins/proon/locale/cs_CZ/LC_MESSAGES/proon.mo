��    |      �  �   �      x
     y
     �
     �
     �
  1   �
     �
     �
  '        *  :  ,    g  �  x  X  a  
   �  $   �     �     �           	          +  
   9     D  	   Q     [     g     p     }     �     �     �     �  �   �  �   [  �   �  '  �  �   �     p  2   �     �  �  �  �   �  m   7  K  �  �   �  g  �      �   1  �   �  Z  �     �!  �  �#     �%     �%     �%     �%     �%     &     &     8&  5   @&     v&     �&     �&  �  �&     h(     w(  	   �(     �(     �(     �(  n  �(     %,  	   >,     H,     V,  ^   _,     �,     �,     �,     �,     �,     -  	   -     -  �   <-     �-     .     .  %  :.     `/  �   |/  x  j0    �1  �   �3  v  �4  I  K;  '  �<     �>    �>     �?     �?     �?     @  =   @  �  T@    4B    @C  \  RE    �F     �H     �H     �H  -   �H  (   �H     I     'I     /I     HI  !   TI  &   vI  +   �I     �I  *  �I     �J     �J     K  	   K     K     :K     @K     TK     qK    sK  �   �L  �  �M  f  SO  	   �P  !   �P     �P     �P     �P     �P     Q     Q     -Q     9Q     QQ     ]Q     cQ     lQ     �Q     �Q     �Q     �Q     �Q  �   �Q  �   fR  �   �R  -  �S  �   �T     \U  4   mU     �U  �  �U  �   �X  a   +Y  B  �Y  �   �Z  2  �[  *  �\  �   _  �   �_  �  n`  �  �a  �  �c     �e     �e     �e     �e     �e     �e     f  	   .f  8   8f     qf     �f     �f  �  �f     �h     �h     �h     �h     �h     �h    i  "   l     8l     Dl  
   Xl  ]   cl  	   �l     �l  	   �l  #   �l     m     &m     Bm  .   Rm  �   �m     ^n     on     �n  �   �n     �o  �   �o  O  �p    �q  �   �r  S  �s  2  �y  �  +{     "}  �   :}     5~     E~     X~     e~  >   {~  �  �~    ��  c  ��  A  ��  u  7�     ��     ��     ��  &   ˆ  #   �     �     �     &�     ;�  !   K�  2   m�  7   ��     ؇         @      ]   U   +   )   t   o   R      r       e   Y          D   y   x      m   P   5       W   f   >       '       9   0   .           T   %      b                p   n   I          ?   ;   g   v   a   3      =   s              G   
                Q   	       \   8             7   2           *           w   h   C   j   S   :          1   z   !          X   #       ,   <       `   6           &   $       _   B       "                         -       V   E   q       H              ^       N       O       (      [   A   L   {      |   M   4                  l   J   K   k   d   c      /   u   Z                        i   F    (none) 2 hours 3 and one third hours 6 and one quarter days 6 and one quarter days plus 3 and one third hours 6 days 6 days plus 2 hours <i>Refresh main </i>Folders<i> list</i> ? A button is normally placed in the SquirrelMail left pane, beneath the list of folders, which enables you to quickly get to this page.  If this box is checked, that button is not drawn in the left pane.  You can still reach this page by selecting 'Options', 'Folder Preferences', and 'Options for Pruning Folders'. A count span counts messages in a folder.  The count may not be negative.  For safety, a value of 0 is treated the same as no value being specified.  Unlike a date span or a size span, a count span is always just a simple numeric value with no additional type of notation. A date span is a relative time duration and is specified as a combination of days and hours.  The overall time duration may not be negative.  For safety, a value of 0 is treated the same as no value being specified.  A simple number is interpreted as a number of days.  If there is a slash ('<code>/</code>'), the number after the slash is interpreted as a number of hours. If days and hours are both specified, they are simply added together.  Some examples are shown in the table below. A size span counts total bytes in a folder.  The size may not be negative.  For safety, a value of 0 is treated the same as no value being specified.  A size consists of a number and an optional suffix character.  The suffix character indicates a multiplier, as shown in the table below.  A number without a suffix gets a default suffix of 'm'. ATTENTION! Action Buttons and Per-Folder Values Before Bottom of Page CAUTION! Consider by Date Consider by Size Count Pruning Count Span Date Pruning Date Span Description Disabled Email Report Enabled First Folder Folder Table Folder doesn't exist. For a 'might be spam' quarantine folder, prune messages older than 30 days, and prune the folder to no more than 2 megabytes.  Again, do not protect unseen messages. For a high-traffic mailing list folder, which you only skim from time to time, prune messages older than a week, including unseen messages. For the 'Drafts' folder, prune anything older than 6 months on the grounds that if you haven't gotten around to finishing a note in that amount of time, you're never going to. For the 'Sent' folder, prune messages older than a week, including unseen messages.  This assumes you don't use your 'Sent' folder as a general collecting area.  If you haven't needed to retrieve something from there in a week (because you forgot to save a copy elsewhere), it can be tossed out. For the 'Trash' folder, prune messages older than 3 days.  Prune the 'Trash' folder to no more than 500 kilobytes or 20 total messages.  Include unseen messages in the pruning. Help and Explanations Here are some examples of fairly typical settings. INBOX If any pruning is requested for the Trash folder along with other folders, this preference controls the ordering.  'First' means that the Trash folder is pruned first, so at the end of a pruning session, it will hold the messages pruned from other folders.  'Last' means that the Trash folder is pruned last, so any messages moved there via pruning will then be subject to a second pruning at the end.  'Natural' means that the Trash folder will be pruned according to its natural order in the list of folders; in other words, it gets no special treatment with respect to ordering.  If no choice is made, the default is 'First'.  This setting makes no practical difference unless 'Prune via Trash' is selected. If disable this box is checked, pruning by message count will not be done.  Any per-folder values for the count span column will still be displayed, but they cannot be updated. If there is both a site setting and a user setting for a given folder, the minimum of the two values is used. If this box is checked, a report summarizing automatic pruning will be made part of the message-list panel.  In contrast to the email notification, a report is made even if no messages were pruned and no errors occurred.  The on-screen notification contains a more verbose version of the same information as the email notification. If this box is checked, a report summarizing automatic pruning will be put into the INBOX as a new message.  An email report is not made if no messages were pruned and no errors occurred. If this box is checked, messages pruned from other folders will be sent to the Trash folder.  Messages pruned from the Trash folder will be discarded.  If this box is not checked, messages pruned from all folders will be discarded immediately.  This setting is independent of the overall SquirrelMail setting for using the Trash folder when deleting messages. If this box is checked, pruning may also consider unsubscribed folders.  If not checked, only subscribed folders are considered, whether for manual pruning or automatic pruning (you can still use the per-folder 'Show Effect' or 'Prune Now' buttons).  This may be handy if you have unsubscribed folders which receive messages in some way other than by manually refiling things to them.  You can only add settings for a folder by subscribing to it, at least temporarily, but settings for unsubscribed folders are used if this box is checked. If this disable box is checked, pruning by message date will not be done.  Any per-folder values for the date span column will still be displayed, but they cannot be updated. If this disable box is checked, pruning by message size will not be done.  Any per-folder values for the size span column will still be displayed, but they cannot be updated. If this item is selected for a given folder, the folder will not be automatically pruned.  It will only be pruned through manual action by you.  Manual action means selecting either 'Prune All Folders' or 'Prune Now' from the pruning options form.  Automatic pruning means sign-on pruning as well as periodic pruning (if that option is selected). If this item is selected for a given folder, unseen (i.e., unread) messages have no special protection against pruning.  If not selected (the default), then the pruning process will not prune any unseen messages in the corresponding folder.  You might consider allowed unseen messages to be pruned from spam quanantine folders and folders which receive mailing list traffic which you don't always read.  You should be especially careful of the date, size, and count spans you specify for folders with this box checked. In effect, this action is the same as automatic pruning, except that it's triggered manually (and email reports are not made).  This action button is similar to the 'Prune Now' action button, except that the entire list of folders (and their invididual settings) is used.  Folders without at least one span value specified are silently skipped.  If some folders have erroneous values, an error message is shown for them, but other (non-error) folders are still pruned. Item Last Left Pane Prune Link Manual Only Messages (pruned): Messages (to prune): Messages automatically pruned: Natural None of the span values has been set for this folder. Nonexistent Folders On-Screen Report Options for Pruning Folders Ordinarily, there is one pruning attempt at SquirrelMail sign-on time.  If you want the sign-on prunings to be done less often, you can specify a number here.  For example, a value of 3 means 'every 3rd sign-on'.  No value specified or a value of 0 means 'every sign-on'.  The local SquirrelMail site administrator may have specified a maximum value for sign-on pruning frequency, in which case that takes precedence if it is lower. Problem with ' Prune All Folders Prune Now Prune via Trash Pruned Pruning ... Pruning can be done manually from this options page, or it can be done periodically and automatically.  This item specifies the recurring time period.  The format is the same as for the date span values for individual folders.  If not specified, no automatic/periodic pruning will be done; so, you can think of this field as an on/off switch for automatic pruning.  For safety, a value of 0 is treated the same as no value specified.  The local SquirrelMail site administrator may have specified a minimum pruning interval, in which case that takes precedence if it is lower.  The recurring interval is measured from the SquirrelMail session sign-on, so automatic pruning attempts will be made at the specified intervals thereafter.  The actual pruning happens coincident with some screen update activity, so an idle SquirrelMail session will not be doing any automatic pruning. Recurring Prune Interval Remainder Same as blank Save All Set a recurring pruning interval of 24 hours, just in case you stay logged on for a long time. Setting Show All Effects Show Effect Sign-on Prune Frequency Site Setting Size Pruning Size Span Size and Count Pruning Order Some folders WERE NOT pruned due to improper date, size, or count spans, or possibly other problems.  See the folder list below for details.  Those without problems WERE pruned. Subscribed Folders The count span is malformed. The date span is malformed. The following table describes user preferences that can affect how pruning is done or not done for you in particular.  The behavior might be changed or limited by site settings controlled by your local administrator.  Descriptions here are in the same order as the User Preferences form above. The size span is malformed. This action button immediately prunes the associated folder.  The number of messages which were pruned is displayed.  If there is not at least one span value specified for the folder, an error message is shown and no messages are pruned. This action button is similar to the 'Show Effect' action button, except that the entire list of folders (and their individual settings) is used.  Folders without at least one span value specified are silently skipped.  The numbers reported for the Trash folder do not take into account any messages that might be sent to the Trash folder as a result of pruning other folders. This action button saves all user preference values and per-folder settings.  If there are errors detected in the user options or per-folder settings, the save is not done.  As an aid to the user, the button has a different appearance when there are known differences between the values on this page and the values that have already been saved in the past.  That really only applies when the page has been redrawn after one of the action buttons.  The button appearance is not dynamically updated as you edit values on the page. This action button simulates pruning of the associated folder.  The number of messages which would have been pruned is displayed.  If there is not at least one span value specified for the folder, an error message is shown. This is an explanation for the user preferences and per-folder data which control selective automatic pruning of folders.  Pruning means the deletion of messages either because they are older than a certain date or to bring a folder to within a certain total size limit or number of messages.<ul><li>Pruning first considers message dates (if there is a user-specified date span value for that folder).  A message's date is the time since it was received by the mail server (this so-called 'internal date' is preserved if you move a message between folders).  Messages are deleted if they have an internal date older than the age indicated by the date span value.</li><li>Pruning next considers total folder size (if there is a user-specified folder size span).  If the folder is over that size limit, additional messages are pruned until the folder is at or below it.</li><li>Pruning finally considers the number of messages in the folder (if there is a user-specified count span).  If a folder has more than that many messages, additional messages are pruned until the folder is at or below the limit.</li></ul><p>In all those pruning cases, unread messages are normally protected and not pruned.  That protection can be removed on a folder-by-folder basis.  Pruning behavior may be flexibly controlled using a variety of other user preferences, each of which is described more fully below.  Unsubscribed and non-existent folders are listed if there is any user preference or site preference given for that folder; this is to avoid a surprise if you suddenly start using a folder of some name and would not otherwise realize that it had pruning options. This options page is normally constructed using colors from the user-chosen SquirrelMail theme, both to make a pleasing display and to highlight important information.  For some themes, this actually makes things on this page difficult to read.  If this box is checked, this options page will be built without most of the colors. This page allows you to conveniently prune messages from any or all folders by using a variety of criteria.  Messages can be pruned manually from this page, or they can be pruned automatically at sign-on and every so often.  Before using the automatic pruning, it would be a good idea to test your settings manually from this page to be sure they do what you want them to do.  Automatic pruning is enabled by giving an appropriate value for the 'Recurring Prune Interval' option, though sign-on pruning is done even if you don't give a value for that. Trash Pruning Order Unless you just clicked on a 'Pruning...' link, you have been automatically brought to this page because your site has installed a SquirrelMail plugin which provides automatic pruning of folders.  By default, no automatic pruning action will happen for you. Unseen, too Unsubscribed Folders Use Theme Colors User Preferences Values were NOT saved due to a problem in one or more fields. When considering which messages to prune by size span and/or by count span, there are two possible orders in which to consider them.  They can be considered by date, in which case messages are pruned from oldest to newest until the size or message count limit for the folder is met.  Or, they can be considered by size, in which case messages are pruned from largest to smallest until the size or message count limit is met.  If neither is selected, the default order is by date. When misconfigured, this tool can delete a lot of messages in a hurry.  If you haven't used it before, you should read through the help and explanations given in the bottom part of this page before you do use it.  Configured properly, it's a safe and convenient tool. You have been brought to this page because one of your SquirrelMail preference items has been automatically converted.  (This is due to a change on this site from using the 'auto_prune_sent' plugin to using the upwardly compatible 'proon' plugin.)  See the entry for the 'Sent' folder in the Folder Table below (scroll down).  Your preferences have already been updated and saved, reflecting the settings as shown.  If you leave things as-is, some messages in your 'Sent' folder may be deleted on future sign-ons to SquirrelMail. You may, of course, change any settings on this page and 'Save All'.  You can return to this page in the future by selecting the 'Pruning...' button (below your list of folders in the left-hand frame) or by selecting a similar link from the 'Options->Folder Preferences' page.  You should not be automatically brought to this page on future logins. Your local SquirrelMail administrator may have specified site settings for one or more options or per-folders items.  In such a case where there is a site setting, it supersedes the user setting (except as noted for particular items below).  Since the site settings are administered separately, your user settings are shown and can be edited even if site settings supersede them.  The site settings, if any, are shown immediately below the corresponding user setting in the User Preferences table and the Folder Table. ^ by Date by Size lowercase, 1,000,000 (the layman's megabytes) lowercase, 1000 (the layman's kilobytes) maximum minimum proon autopruning report same as 'm' uppercase or lowercase, 1 (bytes) uppercase, 1024 (the geek's kilobytes) uppercase, 1024*1024 (the geek's megabytes) yes Project-Id-Version: PACKAGE VERSION
POT-Creation-Date: 2005-08-29 20:52-0700
PO-Revision-Date: 2005-08-31 03:40+0200
Last-Translator: Daniel Kahoun <squirrel@dan.idk.cz>
Language-Team: CZECH <LL@li.org>
MIME-Version: 1.0
Content-Type: text/plain; charset=ISO-8859-2
Content-Transfer-Encoding: 8bit
 (nic) 2 hodiny 3 1/3 hodiny 6 1/4 dne 6 1/4 dne plus 3 1/3 hodiny 6 dn� 6 dn� plus 2 hodiny Obnovit hlavn� seznam slo�ek ? Tla��tko je norm�ln� um�st�no v bo�n�m panelu pod seznamem slo�ek, co� umo��uje rychle zobrazit tuto str�nku. Pokud je pol��ko za�krtnut�, tla��tko se v bo�n�m panelu nezobraz�. Z�st�v� v�ak mo�nost zobrazit tuto str�nku volbou Mo�nosti - Slo�ka - mo�nosti, Mo�nosti promaz�v�n� slo�ek. Po�et je celkov� po�et zpr�v ve slo�ce. Po�et nem��e b�t z�porn�. Pro v�t�� bezpe��, hodnota 0 se chov� stejn�, jakoby nebyla specifikov�na ��dn�. Oproti st��� nebo velikosti je limit po�tu v�dy jednoduch� ��seln� hodnota bez dal��ch pozn�mek. St��� je relativn� doba trv�n� a je ur�ena kombinac� po�tu dne a hodin. Celkov� �as nem��e b�t z�porn�. Pro v�t�� bezpe��, hodnota 0 se chov� stejn�, jakoby nebyla specifikov�na ��dn� hodnota. Jednoduch� ��slo je vylo�eno jako po�et dn�. Pokud je v hodnot� uvedeno lom�tko ('<code>/</code>'), ��slo za lom�tkem je pova�ov�no za po�et hodin. Pokud je ur�en jak po�et dn� tak i po�et hodin, ob� hodnoty jsou se�teny. N�kter� p��klady jsou uvedeny v tabulce n��e. Velikost je sou�et velikost� jednotliv�ch zpr�v ve slo�ce. Zadan� hranice nem��e b�t z�porn�. Pro v�t�� bezpe��, hodnota 0 se chov� stejn�, jakoby nebyla specifikov�na ��dn� hodnota. Velikost se skl�d� z ��sla a voliteln� p��pony. P��pona ur�uje koeficient n�soben�, jak ukazuje tabulka n��e. ��slo bez p��pony m� stejnou hodnotu jako s v�choz� p��ponou 'm'. V�STRAHA! Ak�n� tla��tka a nastaven� slo�ek P�ed Z�pat� POZOR! Podle st��� Podle velikosti Promaz�v�n� podle po�tu Podle po�tu Promaz�v�n� podle st��� Podle st��� Popis Zak�z�no Podat zpr�vu po�tovn� zpr�vou Povoleno Prvn� Slo�ka Tabulka slo�ek Slo�ka neexistuje. Ve slo�ce zpr�v podez�el�ch na spam promaz�vejte zpr�vy star�� t�iceti dn� a tak, aby slo�ka nep�ekro�ila velikost 2 MB. Op�t odstra�te ochranu nep�e�ten�ch zpr�v. Ve slo�ce s vysok�m konferen�n�m provozem, kter� sledujete pouze �as od �asu, promaz�vejte zpr�vy star�� ne� jeden t�den, v�etn� nep�e�ten�ch zpr�v. Ve slo�ce koncept� promaz�vejte cokoli star�� �esti m�s�c�, jestli�e jste je�t� zpr�vu b�hem takov� doby nedokon�ili, u� to stejn� nikdy neud�l�te. Ve slo�ce odeslan� po�ty promaz�vejte zpr�vy star�� ne� jeden t�den, v�etn� nep�e�ten�ch zpr�v. Zde se p�edpokl�d�, �e slo�ku nepou��v�te jako obecnou slo�ku pro ukl�d�n� zpr�v. Pokud nepot�ebujete zachovat zpr�vy star�� ne� jeden t�den (proto�e jste zapomn�li ulo�it kopii jinam), mohou b�t zahozeny. Ve slo�ce odpadkov�ho ko�e promaz�vejte zpr�vy star�� t�� dn�. Zachov�vejte ko� nanejv�� 500 KB nebo 20 zpr�v velk�. Zahr�te nep�e�ten� zpr�vy do promaz�v�n�. N�pov�da a popis Zde je n�kolik p��klad� pom�rn� typick�ch nastaven�. Doru�en� po�ta Pokud je po�adov�no promaz�v�n� pro slo�ku ko�e spolu s jin�mi slo�kami, tato mo�nost ur�uje po�ad�. 'Prvn�m' je m�n�no, �e slo�ka ko�e je promaz�na nejd��ve, tak�e na konci promaz�vac� akce bude ko� obsahovat zpr�vy promazan� z ostatn�ch slo�ek. 'Posledn�m' je m�n�no, �e slo�ka ko�e je promaz�na jako posledn�, tak�e v�echny zpr�vy p�esunut� p�i promaz�v�n� z jin�ch slo�ek do ko�e se stanou tak� p�edm�tem promaz�v�n� z ko�e. 'P�irozen�m' je m�n�no, �e ko� bude promaz�n v z�vislosti jeho p�irozen�ho po�ad� v seznamu slo�ek; jin�mi slovy to nijak neovlivn� po�ad� zpracov�n�. Pokud ��dn� volba nen� provedena, v�choz� je 'Prvn�'. Toto nastaven� nevytv��� ��dn� praktick� rozd�ly nen�-li zvoleno 'Promaz�vat p�es ko�'. Pokud je toto zakazovac� pol��ko za�krtnut�, promaz�v�n� zpr�v podle po�tu nebude provedeno. Nastaven� po�tu zpr�v slo�ek se sice zobraz�, ale nebude mo�n� jej zm�nit. Pokud jsou pro slo�ku pou�ita ob� nastaven�, syst�mov� i u�ivatelsk�, aplikuje se minimum z obou. Je-li toto pol��ko za�krtnut�, vlo�� se do str�nky se zpr�vami ozn�men� s v�sledky automatick�ho promaz�n�. Oproti mo�nosti ozn�men� po�tovn� zpr�vou se toto ozn�men� zobraz� v�dy, i kdy� k ��dn�mu promaz�n� nedojde a ani se nevyskytnou chyby. Toto ozn�men� pouze na obrazovce obsahuje stejn� informace, av�ak podrobn�j��. Je-li toto pol��ko za�krtnut�, vlo�� se do slo�ky nov� po�ty ozn�men� s v�sledky automatick�ho promaz�n� jako oby�ejn� zpr�va. Zpr�va se nevytvo��, pokud b�hem promaz�n� nebyla ��dn� zpr�va odstran�na a pokud nedo�lo k ��dn� chyb�. Pokud je pol��ko za�krtnut�, zpr�vy promazan� z jin�ch slo�ek budou nejd��ve posl�ny do ko�e. Zpr�vy promazan� z ko�e budou zni�eny. Pokud za�krtnuto nen�, zpr�vy ze v�ech slo�ek budou automaticky zni�eny rovnou. Toto nastaven� je nez�visl� na nastaven�, zda pou��vat slo�ku ko� p�i manu�ln�m maz�n� zpr�v. Pokud je pol��ko za�krtnut�, do promaz�v�n� budou tak� zahrnuty nep�ihl�en� slo�ky. Pokud za�krtnut� nen�, zpracov�ny budou pouze p�ihl�en� slo�ky, bez ohledu na to, zda ru�n�m nebo automatick�m promaz�v�n�m (m��ete i nad�le pou��vat akce 'Nane�isto' nebo 'Promazat te�'). To se m��e hodit, pokud m�te nep�ihl�en� slo�ky, do kter�ch p�ij�m�te zpr�vy jinak ne� manu�ln�. Nastaven� neodeb�ran� slo�ky m��ete prov�st pouze tak, �e se alespo� do�asn� k odb�ru p�ihl�s�te, p�i�em� nastaven� z�stanou platn� i po odhl�en�, pokud je toto pol��ko za�krtnut�. Pokud je toto zakazovac� pol��ko za�krtnut�, promaz�v�n� zpr�v podle st��� nebude provedeno. Nastaven� st��� zpr�v slo�ek se sice zobraz�, ale nebude mo�n� jej zm�nit. Pokud je toto zakazovac� pol��ko za�krtnut�, promaz�v�n� zpr�v podle velikosti nebude provedeno. Nastaven� velikosti zpr�v slo�ek se sice zobraz�, ale nebude mo�n� jej zm�nit. Pokud je tato polo�ka vybr�na pro ur�itou slo�ku, slo�ka nebude automaticky promaz�v�na. Budou promaz�na pouze po ru�n�m spu�t�n� akce. Ru�n�m promaz�n�m je my�leno stisknut� tla��tka 'Promazat v�echny slo�ky' or 'Promazat te�' na str�nce nastaven� promaz�v�n�. Automatick�m promaz�v�n�m je my�leno promaz�n� po p�ihl�en� a stejn� tak opakovan� promaz�v�n� (pokud byla mo�nost zvolena). Pokud je polo�ka vybr�na pro n�jakou slo�ku, nep�e�ten� zpr�vy ztrat� speci�ln� ochranu p�ed promaz�n�m. Pokud nen� zvolen� (v�choz� stav), potom promaz�vac� proces nevyma�e ��dn� nep�e�ten� zpr�vy v t�to slo�ce. M�li byste zv�it povolen� smaz�n� nep�e�ten�ch zpr�v z karant�nn�ch slo�ek spamu a ze slo�ek, do kter�ch p�ij�m�te siln� provoz konferenc�, kter� �tete jen ob�asn�. Zejm�na byste m�li b�t obez�etn� p�i nastaven� limitn�ho st���, velikosti �i po�tu, pokud tuto volnu zapnete. Toto ak�n� tla��tko m� stejn� n�sledek, jako proveden� automatick�ho promaz�n� s t�m, �e je spu�t�no ru�n� (a k p��padn�mu oznamen� po�tovn� zpr�vou nedojde). Tla��tko je podobn� tla��tku Promazat te� s t�m, �e se akce aplikuje na v�echny slo�ky (s jejich samostatn�mi nastaven�mi). Slo�ky bez nastaven�ch limit� jsou bez varov�n� p�esko�eny. Pokud p�i promaz�v�n� n�kter�ch slo�ek dojde k chyb�, zobraz� se chybov� zpr�va, ale u slo�ek bez chyby se akce promaz�n� provede. Polo�ka Posledn� Odkaz na bo�n�m panelu Pouze ru�n� Zpr�vy (smazan�): Zpr�vy (ke smaz�n�): Zpr�vy automaticky promaz�ny: P�irozen� Pro tuto slo�ku nebylo stanoveno ��dn� limitn� pravidlo. Neexistuj�c� slo�ky Podat zpr�vu na obrazovce Mo�nosti promaz�v�n� slo�ek Obvykle se prov�d� automatick� promaz�v�n� p�i ka�d�m p�ihl�en�. Pokud si p�ejete prov�d�t promaz�v�n� m�n� �asto, m��ete ur�it del�� interval. Nap��klad hodnota 3 znamen�, �e promaz�v�n� se uskute�n� ka�d� t�et� p�ihl�en�. ��dn� hodnota nebo 0 znamen�, �e se bude promaz�vat p�i ka�d�m p�ihl�en�. Spr�vce syst�mu m��e nastavit omezen� na maxim�ln� hodnotu pro interval promaz�v�n� p�i p�ihl�en�. Pokud spr�vcem ur�en� hodnota bude men�� ne�li V�mi zadan�, bude m�t p�ednost spr�vcova. Probl�m s ' Promazat v�echny slo�ky Promazat te� Promaz�vat p�es ko� Smaz�no Promaz�v�n� ... Promaz�v�n� m��e b�t provedeno manu�ln� z t�to str�nky nebo m��e b�t prov�d�no opakovan� a automaticky. Tato polo�ka ur�uje interval opakovan�ho promaz�v�n�. Form�t je stejn� jako pro limitn� st��� zpr�v u jednotliv�ch slo�ek. Nen�-li ur�ena, nebude se prov�d�t ��dn� automatick� promaz�v�n�; m��ete se tedy na polo�ku d�vat jako na vyp�na� automatick�ho promaz�v�n�. Pro v�t�� bezpe��, hodnota 0 se chov� stejn�, jakoby nebyla specifikov�na ��dn�. Spr�vce m��e omezit minim�ln� d�lku intervalu, kter� m� p�ednost p�ed Va��m nastaven�m. Interval je odvozen od relace, kterou se serverem vedete, od Va�eho p�ihl�en�. K automatick�mu promaz�n� dojde v�dy v ur�en�ch intervalech od p�ihl�en�. Ke spu�t�n� akce promaz�n� dojde jen p�i aktivit� relace, nap�. p�i obnoven� obsahu obrazovky. Interval automatick�ho promaz�v�n� P�ipomenout Stejn� jako pr�zdn� Ulo�it v�e Nastavte promaz�vac� interval na 24 hodin, pouze pro p��pad, �e jste p�ihl�en� dlouhou dobu. Nastaven� V�echny slo�ky nane�isto Nane�isto Interval promaz�v�n� p�i p�ihl�en� Syst�mov� nastaven� Promaz�v�n� podle velikosti Podle velikosti U promaz�v�n� podle velikosti nebo po�tu mazat N�kter� slo�ky nebyly smazan� kv�li nespr�vn�mu datu, velikosti nebo po�tu, p��padn� z jin�ho d�vodu. Podrobnosti jsou uvedeny v seznamu slo�ek n��e (zpr�vy byly smaz�ny ve slo�k�ch, u kter�ch nen� uveden ��dn� probl�m). Odeb�ran� slo�ky Po�et je chybn� vypln�n. St��� je chybn� vypln�no. N�sleduj�c� tabulka popisuje u�ivatelsk� nastaven�, kter� mohou m�t vliv na promaz�v�n�. Chov�n� se m��e m�nit nebo b�t omezeno syst�mov�m nastaven�m spr�vce syst�mu. Popisy zde jsou ve stejn�m po�ad� jako v u�ivatelsk�ch nastaven�ch v��e. Velikost je chybn� vypln�na. Toto ak�n� tla��tko bezprost�edn� proma�e p�idru�enou slo�ku. Po�et zpr�v, kter� byly promaz�ny, bude zobrazen. Pokud pro slo�ku nen� ur�eno ��dn� limitn� pravidlo, zobraz� se chybov� zpr�va a ��dn� zpr�vy se neproma�ou. Toto ak�n� tla��tko je podobn� tla��tku Nane�isto s t�m, �e se provede akce u v�ech slo�ek (podle jejich samostatn�ch nastaven�). Slo�ky bez nastaven�ch limitn�ch hodnot budou p�esko�eny bez varov�n�. V�sledky ozn�men� u odpakov�ho ko�e nebudou obsahovat ty v�sledky, kter� by byly zp�sobeny odstran�n�m zpr�v z jin�ch slo�ek p�es ko�. Toto ak�n� tla��tko ulo�� v�echna u�ivatelsk� nastaven� a limity v�ech slo�ek. Pokud dojde k chyb�, ulo�en� se neprovede. Jako pomoc u�ivatel�m m� tla��tko odli�n� vzhled, pokud jsou zn�my rozd�ly mezi hodnotami zobrazen�mi na str�nce a t�mi ulo�en�mi z minula. Toto ak�n� tla��tko simuluje promaz�n� p�idru�en� slo�ky. Po�et zpr�v, kter� by se akc� promazaly, bude zobrazen. Pokud pro slo�ku nen� ur�eno ��dn� limitn� pravidlo, zobraz� se chybov� zpr�va. N�sleduje v�klad u�ivatelsk�ch nastaven�, kter� ovliv�uj� v�b�rov� automatick� promaz�v�n� slo�ek. Promaz�v�n�m se rozum� smaz�n� zpr�v bu� z d�vodu, �e jsou star�� ne� V�mi uveden� st���, nebo �e slo�ka p�ekro�ila V�mi stanovenou celkovou velikost nebo celkov� po�et zpr�v. <ul><li>Promaz�vac� mechanismus jako prvn� zpracuje zpr�vy podle st��� (je-li vypln�no p��slu�n� pole pro tuto slo�ku). St��� zpr�vy se po��t� od okam�iku doru�en� zpr�vy do po�tovn�ho serveru (tzv. intern� datum je zachov�no i kdy� zpr�vu p�esouv�te mezi slo�kami). Zpr�vy jsou smaz�ny, pokud je jejich intern� datum je star�� ne� uveden� hodnota.</li><li>Promaz�v�n� pokra�uje kontrolou celkov� velikosti slo�ky (pokud byla vypln�na p��slu�n� hodnota). Pokud slo�ka p�ekra�uje stanoven� limit, jsou smaz�ny dal�� zpr�vy, dokud se slo�ka svoj� velikost� nevejde do stanoven�ho limitu.</li><li>Promaz�v�n� je ukon�eno kontrolou celkov�ho po�tu zpr�v ve slo�ce (pokud je p��slu�n� pole vypln�no). Pokud slo�ka obsahuje v�c ne� stanoven� po�et zpr�v, dal�� zpr�vy budou smaz�ny, dokud slo�ka nespln� limit.</ul><p>Ve v�ech t�chto p��padech jsou nep�e�ten� zpr�vy norm�ln� chr�n�ny a nejsou smaz�ny. Tuto ochranu m��ete p�ekonat u jednotliv�ch slo�ek. Chov�n� promaz�v�n� m��e b�t pru�n� ��zeno �adou dal��ch u�ivatelsk�ch nastaven�, ka�d� z nich je podrobn�ji popsan� n��e. Neodeb�ran� a neexistuj�c� slo�ky jsou zobrazeny, pokud se k nim vztahuje jak�koliv u�ivatelsk� nebo syst�mov� nastaven�; toto opat�en� p�edch�z� p��padn�mu p�ekvapen�, pokud za�nete pou��vat slo�ku ur�it�ho n�zvu a nev�imnete si, �e ji� m� promaz�vac� pravidla nastavena. Tato str�nka mo�nost� je norm�ln� sestavena pou��t�m barev vybran�ho barevn�ho sch�matu, jak k vytvo�en� p��jemn�ho vzhledu, tak ke zv�razn�n� d�le�it�ch informac�. U n�kter�ch sch�mat je v�ak obt��n� tuto str�nku p�e��st. Pokud je toto pol��ko za�krtnut�, str�nka bude sestavena bez pou��t� v�t�iny barev. Tato str�nka V�m umo�n� pohodln� promaz�vat zpr�vy z n�kter�ch nebo ze v�ech slo�ek nastaven�m krit�ri�. Zpr�vy mohou b�t promaz�ny ru�n� z t�to str�nky nebo automaticky p�i p�ihl�en� podle pot�eby. P�ed pou�it�m automatick�ho promaz�v�n� si rad�ji sv� nastaven� vyzkou�ejte ru�n�, abyste m�li jistotu, jak se syst�m zachov�. Automatick� promaz�v�n� zah�j�te zad�n�m vhodn� hodnoty do pole Interval automatick�ho promaz�v�n�, nicm�n� promaz�v�n� p�i p�ihl�en� se uskute�n�, i kdy� hodnotu nevypln�te. Po�ad� promaz�v�n� ko�e Pokud jste pr�v� nestiskli tla��tko 'Promaz�v�n�...', tato str�nka se V�m automaticky zobrazila d�ky instalovan�mu modulu, kter� poskytuje mo�nost automatick�ho �i�t�n� Va�ich po�tovn�ch slo�ek. Norm�ln� k ��dn�mu automatick�mu promaz�v�n� nedoch�z�. Tak� nep�e�ten� Neodeb�ran� slo�ky Pou��t barvy U�ivatelsk� nastaven� �daje nebyly ulo�eny kv�li probl�mu v jednom nebo v�ce pol�ch. Kdy� uv��me, kter� zpr�vy budou promaz�ny na z�klad� limitn� velikosti nebo limitn�ho po�tu, m�me dv� mo�n� po�ad�, ve kter�m se promaz�n� zpr�v m��e prov�st. Zpr�vy mohou b�t promaz�ny podle st���, p�i�em� budou nejd��ve maz�ny star�� zpr�vy, dokud se nedos�hne podlimitn� velikosti nebo podlimitn�ho po�tu. Nebo se zpr�vy mohou mazat v po�ad� od nejv�t�� zpr�vy, dokud se nedos�hne limitu. V�choz�m �azen�m je podle st���, nen�-li zvolena ��dn� mo�nost. Pokud nastav�te parametry promaz�v�n� nevhodn�m zp�sobem, m��ete smazat mnoho zpr�v jedin�m kliknut�m. Pokud jste dosud tento n�stroj nepou��vali, m�li byste si nejprve pro��st n�pov�du v doln� ��sti t�to str�nky. Spr�vn� nastaven� je tento n�stroj bezpe�n� a u�ite�n�. Tato str�nka se V�m zobrazila v d�sledku automatick�ho p�evodu nastaven�. (To se mohlo st�t proto, �e se zm�nil modul instalovan� v syst�mu.) Ov��te nastaven� u slo�ky Odeslan� po�ta v tabulce n��e. Tato nastaven� ji� byla upravena pro nov� modul a ulo�ena. Nastaven� m��ete nechat, jak jsou, p�i p���t�m p�ihl�en� budou slo�ky promaz�ny podle nastaven�. M��ete zm�nit libovoln� nastaven� na t�to str�nce a klepnout na Ulo�it v�e. M��ete se pozd�ji vr�tit na tuto str�nku klepnut�m na tla��tko Promaz�v�n� v bo�n�m panelu pod seznamem slo�ek nebo volbou Mo�nosti - Slo�ky - mo�nosti, Promaz�v�n�. P�i p���t�m p�ihl�en� u� by se V�m tato str�nka nem�la automaticky zobrazovat. Spr�vce po�tovn�ho syst�mu SquirrelMail mohl ur�it syst�mov� hodnoty pro jedno nebo v�ce glob�ln�ch nastaven� nebo nastaven� jednotliv�ch slo�ek. V p��pad� spr�vcem ur�en� hodnoty syst�mov�ho nastaven� m� toto nastaven� p�ednost p�ed nastaven�m u�ivatelsk�m (s v�jimkou d�l��ch nastaven� n��e). Proto�e syst�mov� nastaven� jsou spravov�na odd�len�, Va�e u�ivatelsk� nastaven� jsou zobrazena a mohou b�t m�n�na, p�esto�e ta Va�e m��e spr�vce syst�mov�mi nastaven�mi p�ekr�t. Syst�mov� nastaven�, jsou-li k dispozici, najdete hned pod odpov�daj�c�mi u�ivatelsk�mi nastaven�mi v tabulk�ch u�ivatelsk�ch nastaven� a nastaven� slo�ek. ^ podle st��� podle velikosti mal� p�smeno, 1000000 (megabyty laik�) mal� p�smeno, 1000 (kilobyty laik�) maximum minimum ozn�men� o promaz�n� stejn� jako 'm' velk� nebo mal� p�smeno, 1 (byte) velk� p�smeno, 1024 (kilobyty po��ta�ov�ch machr�) velk� p�smeno, 1024*1024 (megabyty po��ta�ov�ch machr�) ano 
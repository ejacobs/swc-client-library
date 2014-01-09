Seals With Clubs Client PHP Library
====================================

Donate: 1BL3sE6rGwR2GHaUNnK9iTHJU6WoYQhTNu

This is not a self playing bot, it merely provides basic protocol functionality. Edit the config file with your username
and password and rename it to config.php. Run the bot.php script at the command line.

This library implements the basic functionality of a Seals With Clubs client (https://www.sealswithclubs.eu). Seals with
Clubs uses the PokerMavens server software and this class should work with any compatible service with some slight modifications.

This software is released as-is with no guarantees. Please be aware of all applicable laws pertaining to online
gambling in your jurisdiction as well as site terms and conditions.


Requirements
------------------------------------
* PHP >=5.3
* curl extension



Why write a long-running socket based script in a language designed for displaying webpages?
------------------------------------
There is no good answer to this question.



Server Response Commands Reference
------------------------------------

* 'Balance'
 - 'Available'
 - 'InPlay'
 - 'Total'


* 'RingGameLobby'
 - 'Clear'
 - 'ID[1..N]
  - 'Seats[N]'
  - 'Type[N]'
  - 'StakesHi[N]'
  - 'StakesLo[N]'
  - 'Players[N]'
  - 'Waiting[N]'
  - 'Game[N]'
  - 'BuyinMax[N]'
  - 'BuyinMin[N]'


* 'TournamentLobby'
 - 'Clear'
 - 'ID[1..N]
  - 'Type[N]'
  - 'Buyin[N]'
  - 'EntryFee[N]'
  - 'Rebuy[N]'
  - 'TS[N]'
  - 'Reg[N]'
  - 'Max[N]'
  - 'Starts[N]'
  - 'StartMin[N]'
  - 'StartTime[N]'
  - 'Tables[N]'
  - 'Password[N]'


* 'Login'
 - 'Status'


* 'Logins'
 - 'Clear'
 - 'LI[1..N]'
  - 'LI[N]' = username|title|location|longTime


* 'LobbyChat'
 - 'Player'
 - 'Text'
 - 'Color'


* 'PlayerInfo'
 - 'Table'
 - 'Type'
 - 'Time'
 - 'Count'
 - 'Player[1..N]'
  - 'Rank[N]'
  - 'Chips[N]'
  - 'NoShow[N]'


* 'Chat'
 - 'Table'
 - 'Player'
 - 'Text'


* 'History'
 - 'Table'
 - 'Hand'


* 'Cards'
 - 'Table'
 - 'Seat'
 - 'Card1'
 - 'Card2'
 - 'Card3'
 - 'Card4'


* 'Table':
 - 'Table'
 - 'Type'
 - 'Seats'
 - 'Dealer'
 - 'Total'
 - 'Password'
 - 'Turn'
 - 'Player[1..N]'
  - 'Location[N]'
  - 'Title[N]'
  - 'Time[N]'
  - 'Chips[N]'
  - 'Bet[N]'
  - 'Avatar[N]'
  - 'Level[N]'
  - 'Custom[N]'
  - 'RealName[N]'
  - 'Gender[N]'
  - 'Action[N]'
  - 'Card1[N]'
  - 'Card2[N]'
  - 'Card3[N]'
  - 'Card4[N]'


* 'HotSeat':
 - 'Seat'


* 'TimeLeft':
 - 'Seat'
 - 'Time'


* 'ActionChips':
 - '$responseArr['Table']);
 - 'Seat'
 - 'Action1'
 - 'Action2'
 - 'Chips']);


* 'Bet':
 - 'Table'
 - 'Seat'
 - 'Bet'


* 'BetCollection':
 - 'Seat[1-9]'


* 'Total':
 - 'Table'
 - 'Total'


* 'Buttons':
 - 'Table'
 - 'Button1'
 - 'Button2'
 - 'Button3'
 - 'Button4'
 - 'MinRaise'
 - 'MaxRaise'
 - 'Type'
 - 'Call'


* 'Flop':
 - 'Table'
 - 'Board1' - Use cardNumToText() to get the textual representation
 - 'Board2' - Use cardNumToText() to get the textual representation
 - 'Board3' - Use cardNumToText() to get the textual representation


* 'Turn'
 - 'Table'
 - 'Board4' - Use cardNumToText() to get the textual representation


* 'River'
 - 'Table'
 - 'Board5' - Use cardNumToText() to get the textual representation


* 'Message'
 - 'Text'


* 'RegisterRequest'
 - 'Table'
 - 'BuyIn'
 - 'Password'


* 'SuspendChat'
 - 'Table'
 - 'Type'
 - 'Suspend'


* 'PotAward'
 - 'Table'
 - 'Type'
 - 'Pot'
 - 'Seat1-9'


* 'Deal'
 - 'Table'
 - 'Type'
 - 'Seats'


* 'Dealer'
 - 'Table'
 - 'Dealer'


* 'TableInfo'
 - 'Type'
 - 'Lines'
 - 'Desc'
 - 'Line1'
 - 'Line2'


* 'TableHeader'
 - 'Table'
 - 'Text'


* 'ECards' - ECards are encrypted, use the decryptCards() function along with the salt and session key to decrypt them
 - 'Card1'
 - 'Card2'
 - 'Card3'
 - 'Card4'
 - 'Salt'


* 'NextMove'
 - 'Table'
 - 'Call'


* 'FoldCards'
 - 'Table'
 - 'Ghost'


* 'Clear'
 - 'Table'


* 'SitOut'
 - 'Table'


* 'OpenTable':
  - 'Table'
  - 'Beep'
  - 'Omaha'


* 'TableMessage':
 - 'Table'
 - 'Text'


* 'HandHelper':
 - 'Table'
 - 'Text'


* 'Time':
 - 'Table'
 - 'Type'
 - 'Button1'
 - 'Button2'
 - 'Button3'
 - 'Call'
 - 'MinRaise'
 - 'MaxRaise'
 - 'IncRaise'
 - 'Bank'


* 'PlayerStats':
 - 'Table'
 - 'Line[1..N]'


* 'Waiting'
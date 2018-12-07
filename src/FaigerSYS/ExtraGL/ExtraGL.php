<?php
namespace FaigerSYS\ExtraGL;

use pocketmine\plugin\PluginBase;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as CLR;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;

use pocketmine\command\ConsoleCommandSender;

class ExtraGL extends PluginBase implements Listener {
	
	/** @var string */
	private $L;
	private $G;
	private $beginL;
	private $beginG;
	private $cache;
	
	/** @var float */
	private $radius;
	
	/** @var int */
	private $beginL_len;
	private $beginG_len;
	
	/** @var ConsoleCommandSender */
	private $ccs;
	
	public function onEnable() {
		$this->getLogger()->info(CLR::GOLD . 'ExtraGL enabling...');
		
		$this->saveResource('config.yml');
		$data = (new Config($this->getDataFolder() . 'config.yml'))->getAll();
		
		$this->L = (string) $data['local'];
		$this->G = (string) $data['global'];
		$this->radius = (float) $data['radius'];
		$this->beginL = (string) $data['begin-local'];
		$this->beginG = (string) $data['begin-global'];
		$this->beginL_len = strlen($this->beginL);
		$this->beginG_len = strlen($this->beginG);
		
		$this->ccs = new ConsoleCommandSender();
		
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		
		$this->getLogger()->info(CLR::GOLD . 'ExtraGL enabled!');
	}
	
	/**
	 * @priority LOWEST
	 */
	public function partOne(PlayerChatEvent $e) {
		$msg = $e->getMessage();
		if (substr($msg, 0, $this->beginG_len) === $this->beginG) {
			$e->setMessage(substr($msg, $this->beginG_len));
			$this->cache = $this->G;
			
		} elseif (substr($msg, 0, $this->beginL_len) === $this->beginL) {
			$e->setMessage(substr($msg, $this->beginL_len));
			
			$sendTo = [$this->ccs];
			$sender = $e->getPlayer();
			
			foreach ($sender->getLevel()->getPlayers() as $player) {
				if ($sender->distance($player) <= $this->radius) {
					$sendTo[] = $player;
				}
			}
			$e->setRecipients($sendTo);
			
			$this->cache = $this->L;
		} else {
			$this->cache = '';
		}
	}
	
	/**
	 * @priority HIGHEST
	 */
	public function partTwo(PlayerChatEvent $e) {
		$e->setFormat($this->cache . $this->getServer()->getLanguage()->translateString($e->getFormat(), [$e->getPlayer()->getDisplayName(), $e->getMessage()]));
	}
	
}

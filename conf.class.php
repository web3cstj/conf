<?php
class Conf {
	static $action = '.';
	static $choix_flags = array(
		'Require'=>array('all granted'),
		'Order'=>array('allow,deny'),
		'AllowOverride'=>array('all', 'none'),
		'Allow'=>array('from all'),
	);
	static $choix_options = array(
		'Indexes',
		'MultiViews',
	);
	public $nom, $directory, $alias, $virtualhost;
	function __construct($values=array()) {
		$this->nom = "";
		$this->directory = new Conf_Directory($values);
		$this->alias = new Conf_Alias($values);
		$this->virtualhost = new Conf_VirtualHost($values);
		foreach ($values as $cle=>$valeur) {
			if (property_exists($this, $cle)) {
				$this->$cle = $valeur;
			}
		}
	}
	static function init($source) {
		$resultat = array();
		if (isset($source['action'])) {
			$conf = new Conf($source);
			if ($source['action'] == 'telecharger') {
				$resultat[] = $conf->generer();
				header('Content-Type: text/plain');
				header('Content-Disposition: attachment; filename="'.$conf->nom.'.conf"');
				echo implode("\n", $resultat);
				exit;
			}
			if ($source['action'] == 'afficher') {
				$resultat[] = $conf->form();
				$resultat[] = '<textarea wrap="off">';
				$resultat[] =  htmlspecialchars($conf->generer());
				$resultat[] = '</textarea>';
			}
		} else {
			$conf = new Conf();
			$resultat[] = $conf->form();
		}
		return implode("\n", $resultat);
	}
	function generer($I='') {
		$resultat = array();
		$resultat[] = $I.'# Avec Wamp, placer ce fichier dans le dossier C:\wamp\alias';
		$resultat[] = $I.'# Avec XAMPP, placer le contenu de ce fichier dans le fichier httpd.conf';
		$resultat[] = $I.'#   du serveur ou y faire un lien (include) vers ce fichier';
		$resultat[] = $I.'# Ne pas oublier de redémarrer le serveur';
		$resultat[] = '';
		$resultat[] = 'LoadModule rewrite_module modules/mod_rewrite.so';
		$resultat[] = '';
		$resultat[] = $I.'# DÉFINITION DE L\'ALIAS';
		//$resultat[] = $I.'RewriteEngine on';
		$resultat[] = $this->alias->generer($I);
		$resultat[] = '';
		$resultat[] = $this->virtualhost->generer($I);
		return implode("\n", $resultat);
	}
	function form($min=false) {
		$resultat = array();
		$resultat[] = '<form class="configsite" action="'.self::$action.'" method="get" >';
		$resultat[] = '<div>Configuration :</div>';
		$resultat[] = $this->form_nom($min);
		$resultat[] = $this->directory->form($min);
		$resultat[] = $this->form_action($min);
		$resultat[] = $this->form_submit($min);
		$resultat[] = '</form>';
		return implode("\n", $resultat);
	}
	function form_submit($min=false) {
		$resultat = array();
		if (!$min) $resultat[] = '<div>';
		$resultat[] = '<input type="submit" value="Générer">';
		if (!$min) {
			$resultat[] = '<a href=".">Recommencer</a>';
			$resultat[] = '</div>';
		}
		return implode("\n", $resultat);
	}
	static function form_nouveau($min=true) {
		$conf = new Conf();
		$conf->nom = '';
		$conf->directory->chemin = '';
		$conf->directory->nom = '';
		$resultat = array();
		$resultat[] = $conf->form($min);
		return implode("\n", $resultat);
	}
	function form_nom($min=false) {
		$label = 'Nom de l\'alias ou du sous-domaine';
		$resultat = array();
		$attributs = 'size="30" type="text" name="nom" id="nom" value="'.$this->nom.'" required="required" pattern="^[a-z][\\.\\-\\_0-9a-z]*$"';
		if ($min) {
			$resultat[] = '<input '.$attributs.' placeholder="'.$label.'" />';
		} else {
			$resultat[] = '<div id="form_nom">';
			$resultat[] = '<span><label for="nom">'.$label.' : </label></span>';
			$resultat[] = '<input '.$attributs.' oninput="form.nomchemin.value=this.value;" />';
			$resultat[] = '</div>';
		}
		return implode("\n", $resultat);
	}
	function form_action($min=false) {
		$value = '';
		$resultat = array();
		if ($min) return implode("\n", $resultat);
		$resultat[] = '<div id="form_action">';
		$resultat[] = '<span>Résultat : ';
		$resultat[] = '<label>';
		if (isset($_GET['action']) && $_GET['action']=="telecharger") {
			$resultat[] = '<input type="radio" name="action" value="telecharger" checked="checked" />';
		} else {
			$resultat[] = '<input type="radio" name="action" value="telecharger" />';
		}
		$resultat[] = ' Télécharger';
		$resultat[] = '</label>';
		$resultat[] = '<label>';
		if (!isset($_GET['action']) || $_GET['action']=="afficher") {
			$resultat[] = '<input type="radio" name="action" value="afficher" checked="checked" />';
		} else {
			$resultat[] = '<input type="radio" name="action" value="afficher" />';
		}
		$resultat[] = ' Afficher';
		$resultat[] = '</label>';
		$resultat[] = '</span>';
		$resultat[] = '</div>';
		return implode("\n", $resultat);
	}
}
class Conf_Directory {
	static $domaine = '.localhost';
	static $choix_flags = array(
		'Require'=>array('all granted'),
		'Order'=>array('allow,deny'),
		'AllowOverride'=>array('all', 'none'),
		'Allow'=>array('from all'),
	);
	static $choix_options = array(
		'Indexes',
		'MultiViews',
		'FollowSymLinks',
		'SymLinksIfOwnerMatch',
		'IncludesNoExec',
	);
	public $chemin, $nom, $flags, $options;
	function __construct($values=array()) {
		$this->chemin = "";
		$this->nom = "";
		$this->flags = array(
			'Require'=>'all granted',
			'Order'=>'allow,deny',
			'AllowOverride'=>'all',
			'Allow'=>'from all',
		);
		$this->options = array(
			'Indexes'=>'+',
			'MultiViews'=>'+',
			'FollowSymLinks'=>'+',
			'SymLinksIfOwnerMatch'=>'+',
			'IncludesNoExec'=>'+',
		);
		foreach ($values as $cle=>$valeur) {
			if (property_exists($this, $cle)) {
				$this->$cle = $valeur;
			}
		}
	}
	function cheminComplet() {
		return str_replace('\\/', '\\', $this->chemin.'/'.$this->nom.'/public');
	}
	function generer($I="") {
		$T = "\t";
		$resultat = array();
		$resultat[] = $I.'<Directory "'.$this->cheminComplet().'">';
		if (count($this->options)) {
			$opt = $I.$T.'Options';
			foreach ($this->options as $nom=>$valeur) {
				if ($valeur!='') {
					$opt .= ' '.$valeur.$nom;
				}
			}
			$resultat[] = $opt;
		}
		foreach ($this->flags as $flag=>$valeur) {
			$resultat[] = $I.$T.$flag.' '.$valeur;
		}
		$resultat[] = $I.'</Directory>';
		return implode("\n", $resultat);
	}
	function form($min=false) {
		$resultat = array();
		if (!$min) {
			$resultat[] = '<fieldset>';
			$resultat[] = '<legend>&lt;Directory&gt;</legend>';
		}
		$resultat[] = $this->form_chemin($min);
		$resultat[] = $this->form_flags($min);
		$resultat[] = $this->form_options($min);
		if (!$min) {
			$resultat[] = '</fieldset>';
		}
		return implode("\n", $resultat);
	}
	function form_chemin($min=false) {
		$label = 'Chemin vers le dossier';
		$attributs = 'size="30" type="text" name="chemin" id="chemin" value="'.$this->chemin.'" required="required"';
		$resultat = array();
		if ($min) {
			$resultat[] = '<input '.$attributs.' placeholder="'.$label.'" />';
		} else {
			$resultat[] = '<div id="form_chemin"><label for="chemin">'.$label.' : </label>';
			$resultat[] = '<input '.$attributs.' />';
			$resultat[] = '/<output name="nomchemin" id="nomchemin">'.$this->nom.'</output>/public';
			$resultat[] = '</div>';
		}
		return implode("\n", $resultat);
	}
	function form_flags($min=false) {
		$resultat = array();
		if ($min) return implode("\n", $resultat);
		$resultat[] = '<fieldset id="form_flags">';
		$resultat[] = '<legend>Directives</legend>';
		foreach (self::$choix_flags as $flag=>$choix) {
			$resultat[] = '<div id="flag_'.$flag.'">';
			$resultat[] = '<label>'.$flag.' : </label>';
			$resultat[] = '<span>';
			if (count($choix)>1) {
				foreach ($choix as $label=>$val) {
					$checked = '';
					if (isset($this->flags[$flag]) && $this->flags[$flag]==$val) {
						$checked = ' checked="checked"';
					}
					$resultat[] = '<label>';
					$resultat[] = '<input type="radio" name="flags['.$flag.']" value="'.$val.'"'.$checked.' />';
					if (is_string($label)) {
						$resultat[] = $label;
					} else {
						$resultat[] = $val;
					}
					$resultat[] = '</label>';
				}
			} else {
				foreach ($choix as $label=>$val) {
					$checked = '';
					if (isset($this->flags[$flag]) && $this->flags[$flag]==$val) {
						$checked = ' checked="checked"';
					}
					$resultat[] = '<label>';
					$resultat[] = '<input type="checkbox" name="flags['.$flag.']" value="'.$val.'"'.$checked.' />';
					if (is_string($label)) {
						$resultat[] = $label;
					} else {
						$resultat[] = $val;
					}
					$resultat[] = '</label>';
				}
			}
			$resultat[] = '</span>';
			$resultat[] = '</div>';
		}
		$resultat[] = '</fieldset>';
		return implode("\n", $resultat);
	}
	function form_options($min=false) {
		$choix = array('Ignorer'=>'','Ajouter'=>'+','Enlever'=>'-',);
		$resultat = array();
		if ($min) return implode("\n", $resultat);
		$resultat[] = '<fieldset id="form_options">';
		$resultat[] = '<legend>Options</legend>';
		foreach (self::$choix_options as $option) {
			$resultat[] = '<div id="option_'.$option.'">';
			$resultat[] = '<label>'.$option.' : </label>';
			$resultat[] = '<span class="groupe">';
			foreach ($choix as $label=>$val) {
				$checked = '';
				if (isset($this->options[$option]) && $this->options[$option]==$val) {
					$checked = ' checked="checked"';
				} else if ($val=='') {
					$checked = ' checked="checked"';
				}
				$resultat[] = '<label>';
				$resultat[] = '<input type="radio" name="options['.$option.']" value="'.$val.'"'.$checked.' />';
				$resultat[] = ' '.$label;
				$resultat[] = '</label>';
			}
			$resultat[] = '</span>';
			$resultat[] = '</div>';
		}
		$resultat[] = '</fieldset>';
		return implode("\n", $resultat);
	}
}
class Conf_Alias {
	public $nom, $directory;
	function __construct($values=array()) {
		$this->nom = "";

		$this->directory = new Conf_Directory($values);
		foreach ($values as $cle=>$valeur) {
			if (property_exists($this, $cle)) {
				$this->$cle = $valeur;
			}
		}
	}
	function generer($I='') {
		$T = "\t";
		$resultat = array();
		$resultat[] = $I.'Alias /'.$this->nom.' '.'"'.$this->directory->cheminComplet().'"';
		$resultat[] = $this->directory->generer($I);
		return implode("\n", $resultat);
	}
}
class Conf_VirtualHost {
	public $nom, $domaine, $directory, $port;
	function __construct($values=array()) {
		$this->port = "80";
		$this->nom = "";
		$this->domaine = ".localhost";

		$this->directory = new Conf_Directory($values);
		foreach ($values as $cle=>$valeur) {
			if (property_exists($this, $cle)) {
				$this->$cle = $valeur;
			}
		}
	}
	function domaineComplet() {
		return $this->nom.$this->domaine;
	}
	function generer($I='') {
		$T = "\t";
		$resultat = array();
		$resultat[] = $I.'# DÉFINITION DE L\'HÔTE VIRTUEL';
		$resultat[] = $I.'<VirtualHost '.$this->domaineComplet().':'.$this->port.'>';
		$resultat[] = $I.$T.'ServerName '.$this->domaineComplet();
		$resultat[] = $I.$T.'DocumentRoot "'.$this->directory->cheminComplet().'"';
//		$resultat[] = $I.$T.'RewriteEngine on';
		$resultat[] = $this->directory->generer($I.$T);
		$resultat[] = $I.$T.'# Ne pas oublier d\'ajouter le code suivant';
		$resultat[] = $I.$T.'# dans le fichier "hosts" :';
		$resultat[] = $I.$T.'# 127.0.0.1'."\t".$this->domaineComplet();
		$resultat[] = $I.$T.'# Le fichier se trouve à l\'endroit suivant :';
		$resultat[] = $I.$T.'# Windows: C:\\WINDOWS\\system32\\drivers\\etc\\hosts';
		$resultat[] = $I.$T.'# Mac: /private/etc/hosts';
		$resultat[] = $I.'</VirtualHost>';
		return implode("\n", $resultat);
	}
}

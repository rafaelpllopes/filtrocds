	<?php
	class BDesus {
		private $db; 
		public function __construct() {
			try {
				$this->db = new PDO("pgsql:host=192.168.50.10 dbname='esus' user=postgres password=esus port=5433");
				$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			} catch (PDOException  $e) {
				print $e->getMessage();
			}
			
		}

		public static function pamOk($str) {
			return isset($str) && strlen($str) > 0;
		}

		public function validarUsuario($cpf, $senha) {
			$sql = 'SELECT nu_cpf, no_pessoa_fisica FROM
			tb_usuario 
			JOIN tb_ator
			ON tb_usuario.co_ator = tb_ator.co_seq_ator
			JOIN tb_pessoa_fisica
			ON tb_ator.co_seq_ator = tb_pessoa_fisica.co_ator
			WHERE nu_cpf = ? AND ds_senha = md5(\'t10-\' || ?)';
			$stmt = $this->db->prepare($sql);
			$stmt->execute(array($cpf, $senha));
			

			return $stmt->fetch(PDO::FETCH_ASSOC);
		}

		private function consultarProducaoDoACSF($codProf, $ano, $mes) {
			$sql = 'SELECT tipo, COUNT(*) FROM (
			SELECT DISTINCT 	
			tb_cds_visita_domiciliar.co_seq_cds_visita_domiciliar as cod_ficha,
			tb_cds_ficha_visita_domiciliar.dt_ficha as dt_ficha,
			tb_pessoa_fisica.nu_cns as prof_cadastrante,
			\'VISITA IND\' as tipo
			FROM tb_cds_visita_domiciliar
			JOIN tb_cds_ficha_visita_domiciliar
			ON tb_cds_ficha_visita_domiciliar.co_seq_cds_ficha_visita_dom = tb_cds_visita_domiciliar.co_cds_ficha_visita_domiciliar 
			JOIN tb_cds_prof
			ON tb_cds_ficha_visita_domiciliar.co_cds_prof = tb_cds_prof.co_seq_cds_prof
			JOIN tb_pessoa_fisica
			ON tb_cds_prof.nu_cns = tb_pessoa_fisica.nu_cns
			JOIN tb_ator
			ON tb_pessoa_fisica.co_ator = tb_ator.co_seq_ator
			UNION ALL
			SELECT DISTINCT
			tb_cds_ficha_visita_domiciliar.co_seq_cds_ficha_visita_dom as cod_ficha,
			tb_cds_ficha_visita_domiciliar.dt_ficha as dt_ficha,
			tb_pessoa_fisica.nu_cns as prof_cadastrante,
			\'VISITA DOMICILIAR\' as tipo
			FROM tb_cds_ficha_visita_domiciliar 
			JOIN tb_cds_prof
			ON tb_cds_ficha_visita_domiciliar.co_cds_prof = tb_cds_prof.co_seq_cds_prof
			JOIN tb_pessoa_fisica
			ON tb_cds_prof.nu_cns = tb_pessoa_fisica.nu_cns
			JOIN tb_ator
			ON tb_pessoa_fisica.co_ator = tb_ator.co_seq_ator
			UNION ALL 
			SELECT DISTINCT
			co_seq_cds_cad_domiciliar as cod_ficha,
			dt_cad_domiciliar as dt_ficha,
			tb_pessoa_fisica.nu_cns as prof_cadastrante,
			\'CADASTRO DOMICILIAR\' as tipo
			FROM tb_cds_cad_domiciliar
			JOIN tb_cds_prof
			ON tb_cds_cad_domiciliar.co_cds_prof_cadastrante = tb_cds_prof.co_seq_cds_prof
			JOIN tb_pessoa_fisica
			ON tb_cds_prof.nu_cns = tb_pessoa_fisica.nu_cns
			JOIN tb_ator
			ON tb_pessoa_fisica.co_ator = tb_ator.co_seq_ator
			UNION ALL
			SELECT DISTINCT
			co_seq_cds_cad_individual as cod_ficha,
			dt_cad_individual as dt_ficha,
			tb_pessoa_fisica.nu_cns as prof_cadastrante,
			\'CADASTRO INDIVIDUAL\' as tipo
			FROM tb_cds_cad_individual
			JOIN tb_cds_prof
			ON tb_cds_cad_individual.co_cds_prof_cadastrante = tb_cds_prof.co_seq_cds_prof
			JOIN tb_pessoa_fisica
			ON tb_cds_prof.nu_cns = tb_pessoa_fisica.nu_cns
			JOIN tb_ator
			ON tb_pessoa_fisica.co_ator = tb_ator.co_seq_ator
		) AS sub WHERE sub.prof_cadastrante = ? AND EXTRACT(YEAR FROM dt_ficha) = ? AND EXTRACT(MONTH FROM dt_ficha) = ?
		GROUP BY tipo
		';
		$stmt = $this->db->prepare($sql);
		$stmt->execute(array($codProf, $ano, $mes));
		
		$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$ret = array();
		foreach($res as $r) {
			$ret[$r['tipo']] = $r['count'];
		}
		return $ret;
		
	}

	private function consultarProducaoDoACS($codProf) {
		$retorno = array();
		for($ano = 2014; $ano <= 2017; ++$ano) {
			for($mes = 1; $mes <= 12; ++$mes) {
				$retorno[$ano][$mes] = $this->consultarProducaoDoACSF($codProf, $ano ,$mes);
			}			
		}
		return $retorno;
	}

	public function consultarProducaoACS() {
		$sql = "
		SELECT * FROM tb_lotacao
		NATURAL JOIN tb_cbo
		JOIN tb_ator_papel ON tb_lotacao.co_ator_papel = tb_ator_papel.co_seq_ator_papel
		JOIN tb_ator ON tb_ator_papel.co_ator = tb_ator.co_seq_ator
		JOIN tb_pessoa_fisica ON tb_ator.co_seq_ator = tb_pessoa_fisica.co_ator
		WHERE tb_cbo.co_cbo_2002 = '515105' AND st_ativo = 1 ORDER BY no_pessoa_fisica
		";
		$stmt = $this->db->prepare($sql);
		$stmt->execute(array());

		$retorno = array();			
		$acses = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach($acses as $acs) {
			$atual = array(
				'profissional' => $acs,
				'producao' => $this->consultarProducaoDoACS($acs['nu_cns'])
				);
			$retorno[] = $atual;
		}
		return $retorno;
	}

	public function consultar($parametros) {
		$condicoes = array();
		if(BDesus::pamOk($parametros['dtInicialFic']))
			$condicoes['DT_FICHA >='] = $parametros['dtInicialFic'];
		if(BDesus::pamOk($parametros['dtFinalFic']))
			$condicoes['DT_FICHA <='] = $parametros['dtFinalFic']." 23:59:59";
		if(BDesus::pamOk($parametros['dtInicialDig']))
			$condicoes['DT_REVISAO >='] = $parametros['dtInicialDig'];
		if(BDesus::pamOk($parametros['dtFinalDig']))
			$condicoes['DT_REVISAO <='] = $parametros['dtFinalDig']." 23:59:59";
		if(BDesus::pamOk($parametros['unidadeCnes']))
			$condicoes['NU_CNES ='] = $parametros['unidadeCnes'];
		if(BDesus::pamOk($parametros['profResponsavel']))
			$condicoes['b.NO_PESSOA_FISICA LIKE '] = '%'.mb_convert_case ($parametros['profResponsavel'],  MB_CASE_UPPER).'%';
		if(BDesus::pamOk($parametros['profDigitador']))
			$condicoes['c.NO_PESSOA_FISICA LIKE '] = '%'.mb_convert_case ($parametros['profDigitador'],  MB_CASE_UPPER).'%';
		
		$montagemSql = '';
		$parametros = array();
		if(!empty($condicoes)) {
			foreach($condicoes as $condicao => $valor) {
				if(strlen($montagemSql))
					$montagemSql .= ' AND ';
				$montagemSql .= $condicao.' ? ';
				$parametros[] = $valor;
			}
		}
		if(strlen($montagemSql) > 0)
			$montagemSql = ' WHERE '.$montagemSql;
		$sql = "SELECT COD_FICHA, DT_FICHA, PROF_CNS, b.NO_PESSOA_FISICA AS PROF_NOME, DIGITADOR_CNS, c.NO_PESSOA_FISICA AS DIGITADOR_NOME, NU_CNES, DT_REVISAO, TIPO FROM (SELECT a.CO_SEQ_CDS_CAD_DOMICILIAR as COD_FICHA, a.DT_CAD_DOMICILIAR as DT_FICHA, d.NU_CNS as PROF_CNS, c.NU_CNS as DIGITADOR_CNS, c.NU_CNES, c.DT_REVISAO, 'CADASTRO DOMICILIAR' as tipo FROM TB_CDS_CAD_DOMICILIAR a INNER JOIN TL_CDS_CAD_DOMICILIAR b ON a.CO_SEQ_CDS_CAD_DOMICILIAR = b.CO_SEQ_CDS_CAD_DOMICILIAR INNER JOIN TB_REVISAO c ON b.CO_REVISAO = c.CO_SEQ_REVISAO INNER JOIN TB_CDS_PROF d ON a.CO_CDS_PROF_CADASTRANTE = d.CO_SEQ_CDS_PROF
		UNION ALL
		SELECT a.CO_SEQ_CDS_FICHA_VISITA_DOM as COD_FICHA, a.DT_FICHA as DT_FICHA, d.NU_CNS as PROF_CNS, c.NU_CNS as DIGITADOR_CNS, c.NU_CNES, c.DT_REVISAO, 'VISITA DOMICILIAR' as tipo FROM TB_CDS_FICHA_VISITA_DOMICILIAR a INNER JOIN TL_CDS_FICHA_VISITA_DOMICILIAR b ON a.CO_SEQ_CDS_FICHA_VISITA_DOM = b.CO_SEQ_CDS_FICHA_VISITA_DOM INNER JOIN TB_REVISAO c ON b.CO_REVISAO = c.CO_SEQ_REVISAO INNER JOIN TB_CDS_PROF d ON a.CO_CDS_PROF = d.CO_SEQ_CDS_PROF
		UNION ALL
		SELECT a.CO_SEQ_CDS_CAD_INDIVIDUAL as COD_FICHA, a.DT_CAD_INDIVIDUAL as DT_FICHA, d.NU_CNS as PROF_CNS, c.NU_CNS as DIGITADOR_CNS, c.NU_CNES, c.DT_REVISAO, 'CADASTRO INDIVIDUAL' as tipo FROM TB_CDS_CAD_INDIVIDUAL a INNER JOIN TL_CDS_CAD_INDIVIDUAL b ON a.CO_SEQ_CDS_CAD_INDIVIDUAL = b.CO_SEQ_CDS_CAD_INDIVIDUAL INNER JOIN TB_REVISAO c ON b.CO_REVISAO = c.CO_SEQ_REVISAO INNER JOIN TB_CDS_PROF d ON a.CO_CDS_PROF_CADASTRANTE = d.CO_SEQ_CDS_PROF) as a LEFT JOIN TB_PESSOA_FISICA b on a.PROF_CNS = b.NU_CNS LEFT JOIN TB_PESSOA_FISICA c on a.DIGITADOR_CNS = c.NU_CNS
		".$montagemSql." GROUP BY DT_FICHA, PROF_CNS, DIGITADOR_CNS, NU_CNES, TIPO,  COD_FICHA, B.NO_PESSOA_FISICA, C.NO_PESSOA_FISICA, DT_REVISAO ORDER BY DT_REVISAO";
		$stmt = $this->db->prepare($sql);
		$stmt->execute($parametros);
		#echo $sql;
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
}

?>

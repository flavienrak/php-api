<?php

// Génération d'une valeur aléatoire avec distribution normale
function randomNormalDistribution($moyenne, $ecartType)
{
  $u = 0;
  $v = 0;
  while ($u === 0) {
    $u = mt_rand() / mt_getrandmax();
  }
  while ($v === 0) {
    $v = mt_rand() / mt_getrandmax();
  }
  $z = sqrt(-2.0 * log($u)) * cos(2.0 * pi() * $v);
  return $z * $ecartType + $moyenne;
}

// Génération d'une nouvelle valeur avec tendance
function generateRandom($valeurPrecedente, $moyenne, $ecartType, $biais)
{
  $variation = round(randomNormalDistribution($moyenne, $ecartType));
  if (mt_rand() / mt_getrandmax() >= $biais) {
    $variation *= -1;
  }
  $valeurPrecedente += $variation;
  $nouvelleValeur = min(max($valeurPrecedente, 0), 100);
  return $nouvelleValeur;
}

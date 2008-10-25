$data = array('Jean Paul Sartre', 'Albert Camus', 'Simone de Beauvoir');

$tags = array('existentialism', 'philosophy', 'french');
$this->cache->set('existentialists', $data, $tags);
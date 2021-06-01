export PATH="/opt/anaconda3/bin:$PATH"
source activate clean3dRS
DIR="$(cd "$(dirname "$0")" && pwd)"
python $DIR/clean.py
conda deactivate
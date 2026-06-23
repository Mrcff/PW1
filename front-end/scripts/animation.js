/**
 * Controla exclusivamente a animação de um sprite sheet 3x3.
 *
 * Linhas: frente (0), lado (1), costas (2).
 * Colunas: os três frames disponíveis para caminhada.
 */
export class Animation {
  static WALK_SEQUENCE = [0, 1, 2, 1];

  /**
   * @param {CanvasImageSource} sprite Sprite sheet já carregado.
   * @param {number} frameWidth Largura de um frame no sheet.
   * @param {number} frameHeight Altura de um frame no sheet.
   * @param {number} speed Frames por segundo durante a caminhada.
   */
  constructor(sprite, frameWidth, frameHeight, speed = 8) {
    if (!sprite) throw new TypeError("Um sprite sheet é obrigatório.");
    if (frameWidth <= 0 || frameHeight <= 0) {
      throw new RangeError("As dimensões do frame devem ser maiores que zero.");
    }
    if (speed <= 0) throw new RangeError("A velocidade deve ser maior que zero.");

    this.sprite = sprite;
    this.frameWidth = frameWidth;
    this.frameHeight = frameHeight;
    this.speed = speed;

    this.direction = "front";
    this.currentFrame = 0;
    this.walking = false;
    this.elapsedTime = 0;
    this.sequenceIndex = 0;
  }

  /**
   * Avança a animação. Deve ser chamado uma vez por frame do loop do jogo.
   * @param {number} deltaTime Tempo desde a última atualização, em milissegundos.
   */
  update(deltaTime = 1000 / 60) {
    if (!this.walking) return;

    this.elapsedTime += deltaTime;
    const frameDuration = 1000 / this.speed;

    while (this.elapsedTime >= frameDuration) {
      this.elapsedTime -= frameDuration;
      this.sequenceIndex =
        (this.sequenceIndex + 1) % Animation.WALK_SEQUENCE.length;
      this.currentFrame = Animation.WALK_SEQUENCE[this.sequenceIndex];
    }
  }

  /**
   * Desenha o frame atual na posição de destino.
   */
  draw(ctx, x, y) {
    const sourceX = this.currentFrame * this.frameWidth;
    const sourceY = this.#getRow() * this.frameHeight;
    const flipHorizontally = this.direction === "left";

    ctx.save();

    if (flipHorizontally) {
      ctx.translate(x + this.frameWidth, y);
      ctx.scale(-1, 1);
      ctx.drawImage(
        this.sprite,
        sourceX,
        sourceY,
        this.frameWidth,
        this.frameHeight,
        0,
        0,
        this.frameWidth,
        this.frameHeight,
      );
    } else {
      ctx.drawImage(
        this.sprite,
        sourceX,
        sourceY,
        this.frameWidth,
        this.frameHeight,
        x,
        y,
        this.frameWidth,
        this.frameHeight,
      );
    }

    ctx.restore();
  }

  setDirection(direction) {
    if (!Object.hasOwn({ front: 0, back: 0, left: 0, right: 0 }, direction)) {
      throw new RangeError(`Direção inválida: ${direction}`);
    }
    this.direction = direction;
  }

  startWalking() {
    this.walking = true;
  }

  stopWalking() {
    this.walking = false;
    this.elapsedTime = 0;
    this.sequenceIndex = 0;
    this.currentFrame = Animation.WALK_SEQUENCE[0];
  }

  #getRow() {
    if (this.direction === "front") return 0;
    if (this.direction === "back") return 2;
    return 1;
  }
}

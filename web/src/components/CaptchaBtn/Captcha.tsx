import React, { useState, useEffect, Fragment } from 'react';
import PropTypes from "prop-types";

const GoCaptcha = (props: any) => {
  const [dots, setDots] = useState([]);
  const [imageBase64Code, setImageBase64Code] = useState('');
  const [thumbBase64Code, setThumbBase64Code] = useState('');
  useEffect(() => {
    let newDots = [];
    let newImageBase64Code = '';
    let newThumbBase64Code = '';

    if (!props.value) {
        newDots = [];
        newImageBase64Code = '';
        newThumbBase64Code = '';
    }

    if (imageBase64Code !== props.imageBase64) {
        newDots = [];
        newImageBase64Code = props.imageBase64;
    }

    if (thumbBase64Code !== props.thumbBase64) {
        newDots = [];
        newThumbBase64Code = props.thumbBase64;
    }

    setDots(newDots);
    setImageBase64Code(newImageBase64Code);
    setThumbBase64Code(newThumbBase64Code);
  }, [props]);

  const handleCloseEvent = () => {
      props.close && props.close();
      setDots([]);
      setImageBase64Code('');
      setThumbBase64Code('');
  };

  const handleRefreshEvent = () => {
      setDots([]);
      props.refresh && props.refresh();
  };

  const handleConfirmEvent = () => {
      props.confirm && props.confirm(dots);
  };

  const handleClickPos = (ev) => {
    const {maxDot} = props;

    if (dots.length >= maxDot) {
        return false;
    }
    const e = ev || window.event;
    e.preventDefault();
    const dom = e.currentTarget;

    const {domX, domY} = getDomXY(dom);
    // ===============================================
    // @notice 如 getDomXY 不准确可尝试使用 calcLocationLeft 或 calcLocationTop
    // const domX = this.calcLocationLeft(dom)
    // const domY = this.calcLocationTop(dom)
    // ===============================================
    let mouseX = (navigator.appName === 'Netscape') ? e.pageX : e.x + document.body.offsetTop;
    let mouseY = (navigator.appName === 'Netscape') ? e.pageY : e.y + document.body.offsetTop;
    if (props.calcPosType === 'screen') {
        mouseX = (navigator.appName === 'Netscape') ? e.clientX : e.x;
        mouseY = (navigator.appName === 'Netscape') ? e.clientY : e.y;
    }

    // 计算点击的相对位置
    const xPos = mouseX - domX;
    const yPos = mouseY - domY;

    // 转整形
    const xp = parseInt(xPos.toString());
    const yp = parseInt(yPos.toString());

    // 减去点的一半
    const newDots = [...dots, {
        x: xp - 11,
        y: yp - 11,
        index: dots.length + 1
    }];
    setDots(newDots);
    return false;
  };
  /**
   * @Description: 找到元素的屏幕位置
   * @param el
   */
  const calcLocationLeft = (el) => {
    let tmp = el.offsetLeft;
    let val = el.offsetParent;
    while (val != null) {
        tmp += val.offsetLeft;
        val = val.offsetParent;
    }
    return tmp;
  };

  /**
   * @Description: 找到元素的屏幕位置
   * @param el
   */
  const calcLocationTop = (el) => {
    let tmp = el.offsetTop;
    let val = el.offsetParent;
    while (val != null) {
        tmp += val.offsetTop;
        val = val.offsetParent;
    }
    return tmp;
  };

  const getDomXY = (dom) => {
    let x = 0;
    let y = 0;
    if (dom.getBoundingClientRect) {
      let box = dom.getBoundingClientRect();
      let D = document.documentElement;
      x = box.left + Math.max(D.scrollLeft, document.body.scrollLeft) - D.clientLeft;
      y = box.top + Math.max(D.scrollTop, document.body.scrollTop) - D.clientTop;
    } else{
      while (dom !== document.body) {
        x += dom.offsetLeft;
        y += dom.offsetTop;
        dom = dom.offsetParent;
      }
    }
    return {
      domX: x,
      domY: y
    };
  };

  const RenderDotItem = () => {
    return dots.map((dot) => (
      <Fragment key={dot.index}>
        <div className="wg-cap-wrap__dot" style={{top: `${dot.y}px`, left: `${dot.x}px`}}>
          <span>{dot.index}</span>
        </div>
      </Fragment>
    ));
  };

  return (
    <>
      <div className="wg-cap-wrap">
        <div className="wg-cap-wrap__header">
          <span>请在下图<em>依次</em>点击：</span>
          {
            props.thumbBase64 && <img className="wg-cap-wrap__thumb" src={props.thumbBase64} alt=" "/>
          }
        </div>
        <div className="wg-cap-wrap__body" style={{
          width: props.width,
          height: props.height
        }}>
          {
            props.imageBase64 && <img className="wg-cap-wrap__picture"
                  src={props.imageBase64} alt=" "
                  onClick={handleClickPos}/>
          }
          <img className="wg-cap-wrap__loading"
            src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiBzdHlsZT0ibWFyZ2luOiBhdXRvOyBiYWNrZ3JvdW5kOiByZ2JhKDI0MSwgMjQyLCAyNDMsIDApOyBkaXNwbGF5OiBibG9jazsgc2hhcGUtcmVuZGVyaW5nOiBhdXRvOyIgd2lkdGg9IjY0cHgiIGhlaWdodD0iNjRweCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIHByZXNlcnZlQXNwZWN0UmF0aW89InhNaWRZTWlkIj4KICA8Y2lyY2xlIGN4PSI1MCIgY3k9IjM2LjgxMDEiIHI9IjEzIiBmaWxsPSIjM2U3Y2ZmIj4KICAgIDxhbmltYXRlIGF0dHJpYnV0ZU5hbWU9ImN5IiBkdXI9IjFzIiByZXBlYXRDb3VudD0iaW5kZWZpbml0ZSIgY2FsY01vZGU9InNwbGluZSIga2V5U3BsaW5lcz0iMC40NSAwIDAuOSAwLjU1OzAgMC40NSAwLjU1IDAuOSIga2V5VGltZXM9IjA7MC41OzEiIHZhbHVlcz0iMjM7Nzc7MjMiPjwvYW5pbWF0ZT4KICA8L2NpcmNsZT4KPC9zdmc+"
            alt="正在加载中..."/>
          <RenderDotItem/>
        </div>
        <div className="wg-cap-wrap__footer">
          <div className="wg-cap-wrap__ico">
            <img onClick={handleCloseEvent}
              src="data:image/svg+xml;base64,PHN2ZyB0PSIxNjI2NjE0NDM5NDIzIiBjbGFzcz0iaWNvbiIgdmlld0JveD0iMCAwIDEwMjQgMTAyNCIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHAtaWQ9Ijg2NzUiIHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIj48cGF0aCBkPSJNNTEyIDIzLjI3MjcyN2MyNjkuOTE3MDkxIDAgNDg4LjcyNzI3MyAyMTguODEwMTgyIDQ4OC43MjcyNzMgNDg4LjcyNzI3M2E0ODYuNjMyNzI3IDQ4Ni42MzI3MjcgMCAwIDEtODQuOTQ1NDU1IDI3NS40MDk0NTUgNDYuNTQ1NDU1IDQ2LjU0NTQ1NSAwIDAgMS03Ni44NDY1NDUtNTIuNTI2NTQ2QTM5My41NDE4MTggMzkzLjU0MTgxOCAwIDAgMCA5MDcuNjM2MzY0IDUxMmMwLTIxOC41MDc2MzYtMTc3LjEyODcyNy0zOTUuNjM2MzY0LTM5NS42MzYzNjQtMzk1LjYzNjM2NFMxMTYuMzYzNjM2IDI5My40OTIzNjQgMTE2LjM2MzYzNiA1MTJzMTc3LjEyODcyNyAzOTUuNjM2MzY0IDM5NS42MzYzNjQgMzk1LjYzNjM2NGEzOTUuMTcwOTA5IDM5NS4xNzA5MDkgMCAwIDAgMTI1LjQ0LTIwLjI5MzgxOSA0Ni41NDU0NTUgNDYuNTQ1NDU1IDAgMCAxIDI5LjQ4NjU0NSA4OC4yOTY3MjhBNDg4LjI2MTgxOCA0ODguMjYxODE4IDAgMCAxIDUxMiAxMDAwLjcyNzI3M0MyNDIuMDgyOTA5IDEwMDAuNzI3MjczIDIzLjI3MjcyNyA3ODEuOTE3MDkxIDIzLjI3MjcyNyA1MTJTMjQyLjA4MjkwOSAyMy4yNzI3MjcgNTEyIDIzLjI3MjcyN3ogbS0xMTUuMiAzMDcuNzEyTDUxMiA0NDYuMTM4MTgybDExNS4yLTExNS4yYTQ2LjU0NTQ1NSA0Ni41NDU0NTUgMCAxIDEgNjUuODE1MjczIDY1Ljg2MTgxOEw1NzcuODYxODE4IDUxMmwxMTUuMiAxMTUuMmE0Ni41NDU0NTUgNDYuNTQ1NDU1IDAgMSAxLTY1Ljg2MTgxOCA2NS44MTUyNzNMNTEyIDU3Ny44NjE4MThsLTExNS4yIDExNS4yYTQ2LjU0NTQ1NSA0Ni41NDU0NTUgMCAxIDEtNjUuODE1MjczLTY1Ljg2MTgxOEw0NDYuMTM4MTgyIDUxMmwtMTE1LjItMTE1LjJhNDYuNTQ1NDU1IDQ2LjU0NTQ1NSAwIDEgMSA2NS44NjE4MTgtNjUuODE1MjczeiIgcC1pZD0iODY3NiIgZmlsbD0iIzcwNzA3MCI+PC9wYXRoPjwvc3ZnPg=="
              alt="关闭"/>
            <img onClick={handleRefreshEvent}
              src="data:image/svg+xml;base64,PHN2ZyB0PSIxNjI2NjE0NDk5NjM4IiBjbGFzcz0iaWNvbiIgdmlld0JveD0iMCAwIDEwMjQgMTAyNCIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHAtaWQ9IjEzNjAiIHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIj48cGF0aCBkPSJNMTg3LjQ1NiA0MjUuMDI0YTMzNiAzMzYgMCAwIDAgMzY4LjM4NCA0MjAuMjI0IDQ4IDQ4IDAgMCAxIDEyLjU0NCA5NS4xNjggNDMyIDQzMiAwIDAgMS00NzMuNjY0LTU0MC4xNmwtNTcuMjgtMTUuMzZhMTIuOCAxMi44IDAgMCAxLTYuMjcyLTIwLjkyOGwxNTkuMTY4LTE3OS40NTZhMTIuOCAxMi44IDAgMCAxIDIyLjE0NCA1Ljg4OGw0OC4wNjQgMjM1LjA3MmExMi44IDEyLjggMCAwIDEtMTUuODA4IDE0LjkxMmwtNTcuMjgtMTUuMzZ6TTgzNi40OCA1OTkuMDRhMzM2IDMzNiAwIDAgMC0zNjguMzg0LTQyMC4yMjQgNDggNDggMCAxIDEtMTIuNTQ0LTk1LjE2OCA0MzIgNDMyIDAgMCAxIDQ3My42NjQgNTQwLjE2bDU3LjI4IDE1LjM2YTEyLjggMTIuOCAwIDAgMSA2LjI3MiAyMC45MjhsLTE1OS4xNjggMTc5LjQ1NmExMi44IDEyLjggMCAwIDEtMjIuMTQ0LTUuODg4bC00OC4wNjQtMjM1LjA3MmExMi44IDEyLjggMCAwIDEgMTUuODA4LTE0LjkxMmw1Ny4yOCAxNS4zNnoiIGZpbGw9IiM3MDcwNzAiIHAtaWQ9IjEzNjEiPjwvcGF0aD48L3N2Zz4="
              alt="刷新"/>
          </div>
          <div className="wg-cap-wrap__btn">
            <button onClick={handleConfirmEvent}>确认</button>
          </div>
        </div>
      </div>
    </>
  );
};

GoCaptcha.propTypes = {
  value: PropTypes.bool.isRequired,
  width: PropTypes.string,
  height: PropTypes.string,
  calcPosType: PropTypes.oneOf(['dom', 'screen']),
  maxDot: PropTypes.number,
  imageBase64: PropTypes.string,
  thumbBase64: PropTypes.string,
  close: PropTypes.func,
  refresh: PropTypes.func,
  confirm: PropTypes.func,
};

GoCaptcha.defaultProps = {
  width: '300px',
  height: '240px',
  maxDot: 5
};

export default GoCaptcha;

using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class touchlogic : MonoBehaviour {
	public float perspectiveZoomSpeed = 5f;
	public float orthoZoomSpeed = 5f;
	public bool isorthographic;
	Camera cam;

	void Update(){
		if (Input.touchCount == 2) {
			Touch touchzero = Input.GetTouch (0);
			Touch touchOne = Input.GetTouch (1);
			Vector2 touchZeroPrevPos = touchzero.position - touchzero.deltaPosition;
			Vector2 touchOnePrevPos = touchOne.position - touchzero.deltaPosition;
			float prevTouchDeltaMag = (touchZeroPrevPos - touchOnePrevPos).magnitude;
			float touchDeltaMag = (touchzero.position - touchOne.position).magnitude;
			float deltaMagnitudediff = prevTouchDeltaMag - touchDeltaMag;
			if (cam.orthographic) {
				cam.orthographicSize += deltaMagnitudediff * orthoZoomSpeed;
				cam.orthographicSize = Mathf.Max (cam.orthographicSize, 1f);
			} else {
				cam.fieldOfView += deltaMagnitudediff * perspectiveZoomSpeed;
				cam.fieldOfView = Mathf.Clamp (cam.fieldOfView, 1f, 179.9f);
			}
		}
	}
	}


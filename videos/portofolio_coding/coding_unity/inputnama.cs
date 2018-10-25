using System.Collections;
using System.Collections.Generic;
using UnityEngine;
using UnityEngine.UI;
public class inputnama : MonoBehaviour {
	public InputField name;

	public Text fText;

	// Use this for initialization
	public void setget(){
		if (name.text == "") {
			name.text = "guest";
		}
		fText.text = name.text;

	}
	void Start () {
		
	}
	
	// Update is called once per frame
	void Update () {
		
	}
}

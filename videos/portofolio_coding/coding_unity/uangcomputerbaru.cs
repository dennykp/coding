using System.Collections;
using System.Collections.Generic;
using UnityEngine;
using UnityEngine.UI;
public class uangcomputerbaru : MonoBehaviour {
	public static double uangcomputer;
	Text textuangcomputer;
	// Use this for initialization
	void Start () {
		
	}
	void Awake(){
		textuangcomputer = GetComponent<Text> ();
		uangcomputer = 0000;
	}
	// Update is called once per frame
	void Update () {
		textuangcomputer.text = "" + uangcomputer;
		if (uangcomputer < 0) {
			textuangcomputer.text = "Bangkrut";
		}
	}
}

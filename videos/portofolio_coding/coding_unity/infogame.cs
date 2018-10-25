using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class infogame : MonoBehaviour {
	public GameObject panelpenjara;
	public GameObject panelbacktojail;
	public GameObject panelbonusstart;
	public GameObject panelbonusuang;

	public GameObject panelpajak;
	public GameObject panelbangunrumah;
	public GameObject panelsoal;
	// Use this for initialization
	void Start () {
		
	}
	
	// Update is called once per frame
	void Update () {
		
	}
	public void clickpenjara(){
		panelpenjara.SetActive (true);
		panelbacktojail.SetActive (false);
		panelbonusuang.SetActive (false);
		panelbonusstart.SetActive (false);
		panelpajak.SetActive (false);
		panelbangunrumah.SetActive (false);
		panelsoal.SetActive (false);
	}
	public void clickbacktojail(){
		panelbacktojail.SetActive (true);
		panelpenjara.SetActive (false);
		panelbonusuang.SetActive (false);
		panelbonusstart.SetActive (false);
		panelpajak.SetActive (false);
		panelbangunrumah.SetActive (false);
		panelsoal.SetActive (false);
	}
	public void clickbonusstart(){
		panelpenjara.SetActive (false);
		panelbacktojail.SetActive (false);
		panelbonusuang.SetActive (false);
		panelbonusstart.SetActive (true);
		panelpajak.SetActive (false);
		panelbangunrumah.SetActive (false);
		panelsoal.SetActive (false);
	}
	public void clickbonusuang(){
		panelpenjara.SetActive (false);
		panelbacktojail.SetActive (false);
		panelbonusuang.SetActive (true);
		panelbonusstart.SetActive (false);
		panelpajak.SetActive (false);
		panelbangunrumah.SetActive (false);
		panelsoal.SetActive (false);
	}
	public void clickpajak(){
		panelpenjara.SetActive (false);
		panelbacktojail.SetActive (false);
		panelbonusuang.SetActive (false);
		panelbonusstart.SetActive (false);
		panelpajak.SetActive (true);
		panelbangunrumah.SetActive (false);
		panelsoal.SetActive (false);
	}
	public void clickbangunrumah(){
		panelpenjara.SetActive (false);
		panelbacktojail.SetActive (false);
		panelbonusuang.SetActive (false);
		panelbonusstart.SetActive (false);
		panelpajak.SetActive (false);
		panelbangunrumah.SetActive (true);
		panelsoal.SetActive (false);
	}
	public void clicksoal(){
		panelpenjara.SetActive (false);
		panelbacktojail.SetActive (false);
		panelbonusuang.SetActive (false);
		panelbonusstart.SetActive (false);
		panelpajak.SetActive (false);
		panelbangunrumah.SetActive (false);
		panelsoal.SetActive (true);
	}
}
